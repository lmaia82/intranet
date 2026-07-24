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

class AdminAtualizarDatasCriacaoDoAdTest extends TestCase
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

    public function test_admin_atualiza_created_at_dos_usuarios_vinculados_ao_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);
        Sector::create(['sigla' => 'TI']);

        $usuario = User::factory()->create([
            'email' => 'fulano@cetem.gov.br',
            'ad_guid' => (string) Str::orderedUuid(),
            'created_at' => now(),
        ]);

        $fake = DirectoryEmulator::setup();

        $criadoNoAd = Carbon::parse('2018-03-10 09:00:00');

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'objectguid' => Str::orderedUuid(),
            'useraccountcontrol' => 512,
            'whencreated' => $criadoNoAd->format('YmdHis\Z'),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('admin@cetem.gov.br');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.atualizar-datas-criacao-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $response->assertRedirect(route('admin.usuarios'));

        $this->assertEquals($criadoNoAd->toDateString(), $usuario->fresh()->created_at->toDateString());
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

        $this->actingAs($admin)->post(route('admin.usuarios.atualizar-datas-criacao-do-ad'), [
            'password' => 'senha-do-admin',
        ]);

        $this->assertEquals($dataOriginal->toDateString(), $usuarioLocal->fresh()->created_at->toDateString());
    }

    public function test_senha_incorreta_do_admin_cancela_a_atualizacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@cetem.gov.br']);

        DirectoryEmulator::setup();

        // Sem shouldAllowBindWith: nenhum bind é aceito.

        $response = $this->actingAs($admin)->post(route('admin.usuarios.atualizar-datas-criacao-do-ad'), [
            'password' => 'senha-errada',
        ]);

        $response->assertRedirect(route('admin.usuarios'));
    }

    public function test_usuario_nao_admin_nao_atualiza_datas(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.usuarios.atualizar-datas-criacao-do-ad'), [
            'password' => 'qualquer',
        ])->assertForbidden();
    }
}
