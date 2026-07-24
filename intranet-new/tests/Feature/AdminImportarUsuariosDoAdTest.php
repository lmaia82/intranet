<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Tests\TestCase;

class AdminImportarUsuariosDoAdTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        DirectoryEmulator::tearDown();

        parent::tearDown();
    }

    private function criarUsuarioNoAd(array $atributos, string $setor): LdapUser
    {
        $usuario = new LdapUser($atributos);
        $usuario->inside("OU={$setor},OU=CETEM,DC=mineral,DC=cetem");
        $usuario->save();

        return $usuario;
    }

    public function test_admin_importa_usuarios_ativos_do_ad_que_ainda_nao_existem(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        $setorTi = Sector::create(['sigla' => 'TI']);
        Group::create(['name' => 'Leitores']);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Novo da Silva',
            'mail' => 'novo@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $response->assertRedirect(route('admin.usuarios'));

        $novoUsuario = User::where('email', 'novo@cetem.gov.br')->first();
        $this->assertNotNull($novoUsuario);
        $this->assertSame('Novo da Silva', $novoUsuario->name);
        $this->assertSame('TI', $novoUsuario->ad_setor);
        $this->assertSame($setorTi->id, $novoUsuario->sector_id);
        $this->assertSame('Leitores', $novoUsuario->group->name);
    }

    public function test_importacao_ja_traz_data_de_criacao_e_expiracao_do_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        Sector::create(['sigla' => 'TI']);
        Group::create(['name' => 'Leitores']);

        $fake = DirectoryEmulator::setup();

        $criadoNoAd = Carbon::parse('2019-05-20 12:00:00', 'UTC');
        $expiraNoAd = Carbon::parse('2028-01-10 12:00:00', 'UTC');

        $this->criarUsuarioNoAd([
            'cn' => 'Novo da Silva',
            'mail' => 'novo@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
            'whencreated' => $criadoNoAd->format('YmdHis\Z'),
            'accountexpires' => (string) (($expiraNoAd->timestamp + 11644473600) * 10000000),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $this->actingAs($admin)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $novoUsuario = User::where('email', 'novo@cetem.gov.br')->firstOrFail();
        $this->assertEquals($criadoNoAd->toDateString(), $novoUsuario->created_at->toDateString());
        $this->assertEquals($expiraNoAd->toDateString(), $novoUsuario->ad_expira_em->toDateString());
    }

    public function test_nao_reimporta_usuario_que_ja_existe_na_intranet(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        $setorJaDefinido = Sector::create(['sigla' => 'RH']);
        $grupoJaDefinido = Group::create(['name' => 'Editores']);

        $existente = User::factory()->create([
            'email' => 'existente@cetem.gov.br',
            'sector_id' => $setorJaDefinido->id,
            'group_id' => $grupoJaDefinido->id,
        ]);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Nome Diferente No Ad',
            'mail' => 'existente@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $this->actingAs($admin)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $this->assertSame(1, User::where('email', 'existente@cetem.gov.br')->count());

        $existente->refresh();
        $this->assertSame($setorJaDefinido->id, $existente->sector_id);
        $this->assertSame($grupoJaDefinido->id, $existente->group_id);
        $this->assertNull($existente->ad_guid);
    }

    public function test_senha_incorreta_do_admin_cancela_a_importacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Novo da Silva',
            'mail' => 'novo@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
        ], setor: 'TI');

        // Sem shouldAllowBindWith: nenhum bind é aceito.

        $response = $this->actingAs($admin)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'senha-errada',
        ]);

        $response->assertRedirect(route('admin.usuarios'));
        $this->assertNull(User::where('email', 'novo@cetem.gov.br')->first());
    }

    public function test_restaura_o_custo_do_bcrypt_apos_a_importacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        Sector::create(['sigla' => 'TI']);
        Group::create(['name' => 'Leitores']);

        $custoOriginal = config('hashing.bcrypt.rounds');

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Novo da Silva',
            'mail' => 'novo@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $this->actingAs($admin)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $this->assertSame($custoOriginal, config('hashing.bcrypt.rounds'));
    }

    public function test_ad_com_dois_objetos_para_o_mesmo_email_nao_interrompe_a_importacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        Sector::create(['sigla' => 'TI']);
        Group::create(['name' => 'Leitores']);

        $fake = DirectoryEmulator::setup();

        // Dado real de diretório desatualizado: dois objetos do AD com o
        // mesmo "mail" (o segundo é um resquício/duplicata) — não pode
        // travar a importação inteira com um erro 500.
        $this->criarUsuarioNoAd([
            'cn' => 'Duplicado Um',
            'mail' => 'duplicado@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
        ], setor: 'TI');

        $this->criarUsuarioNoAd([
            'cn' => 'Duplicado Dois',
            'mail' => 'duplicado@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
        ], setor: 'TI');

        $this->criarUsuarioNoAd([
            'cn' => 'Depois Do Duplicado',
            'mail' => 'depois@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $response->assertRedirect(route('admin.usuarios'));
        $this->assertSame(1, User::where('email', 'duplicado@cetem.gov.br')->count());
        $this->assertNotNull(User::where('email', 'depois@cetem.gov.br')->first());
    }

    public function test_usuario_nao_admin_nao_importa_do_ad(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'qualquer',
        ])->assertForbidden();
    }
}
