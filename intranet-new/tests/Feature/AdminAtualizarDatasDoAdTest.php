<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Tests\TestCase;

class AdminAtualizarDatasDoAdTest extends TestCase
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

    private function windowsInt(Carbon $data): string
    {
        return (string) (($data->timestamp + 11644473600) * 10000000);
    }

    public function test_admin_atualiza_created_at_e_expira_em_dos_usuarios_vinculados_ao_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        Sector::create(['sigla' => 'TI']);

        $usuario = User::factory()->create([
            'email' => 'fulano@cetem.gov.br',
            'ad_guid' => (string) Str::orderedUuid(),
            'created_at' => now(),
            'ad_expira_em' => null,
        ]);

        $fake = DirectoryEmulator::setup();

        $criadoNoAd = Carbon::parse('2018-03-10 12:00:00', 'UTC');
        $expiraNoAd = Carbon::parse('2027-06-15 12:00:00', 'UTC');

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
            'whencreated' => $criadoNoAd->format('YmdHis\Z'),
            'accountexpires' => $this->windowsInt($expiraNoAd),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.atualizar-datas-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $response->assertRedirect(route('admin.usuarios'));

        $usuario->refresh();
        $this->assertEquals($criadoNoAd->toDateString(), $usuario->created_at->toDateString());
        $this->assertEquals($expiraNoAd->toDateString(), $usuario->ad_expira_em->toDateString());
    }

    public function test_conta_sem_expiracao_no_ad_fica_com_ad_expira_em_nulo(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        Sector::create(['sigla' => 'TI']);

        $usuario = User::factory()->create([
            'email' => 'fulano@cetem.gov.br',
            'ad_guid' => (string) Str::orderedUuid(),
            'ad_expira_em' => now(),
        ]);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
            'whencreated' => now()->format('YmdHis\Z'),
            'accountexpires' => '0',
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $this->actingAs($admin)->post(route('admin.usuarios.atualizar-datas-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $this->assertNull($usuario->fresh()->ad_expira_em);
    }

    public function test_nao_atualiza_usuario_sem_vinculo_com_o_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        Sector::create(['sigla' => 'TI']);

        $dataOriginal = now()->subYear();
        $usuarioLocal = User::factory()->create([
            'email' => 'local@cetem.gov.br',
            'ad_guid' => null,
            'created_at' => $dataOriginal,
        ]);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Local',
            'mail' => 'local@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
            'whencreated' => now()->format('YmdHis\Z'),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $this->actingAs($admin)->post(route('admin.usuarios.atualizar-datas-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $this->assertEquals($dataOriginal->toDateString(), $usuarioLocal->fresh()->created_at->toDateString());
        $this->assertNull($usuarioLocal->fresh()->ad_expira_em);
    }

    public function test_senha_incorreta_do_admin_cancela_a_atualizacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);

        DirectoryEmulator::setup();

        // Sem shouldAllowBindWith: nenhum bind é aceito.

        $response = $this->actingAs($admin)->post(route('admin.usuarios.atualizar-datas-do-ad'), [
            'password' => 'senha-errada',
        ]);

        $response->assertRedirect(route('admin.usuarios'));
    }

    public function test_usuario_nao_admin_nao_atualiza_datas(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.usuarios.atualizar-datas-do-ad'), [
            'password' => 'qualquer',
        ])->assertForbidden();
    }
}
