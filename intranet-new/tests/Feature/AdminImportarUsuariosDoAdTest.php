<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_usuario_nao_admin_nao_importa_do_ad(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.usuarios.importar-do-ad'), [
            'password' => 'qualquer',
        ])->assertForbidden();
    }
}
