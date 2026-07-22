<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Testing\LdapFake;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestCase;

class ActiveDirectoryAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        DirectoryEmulator::tearDown();

        parent::tearDown();
    }

    public function test_usuario_do_ad_autentica_via_bind_direto_com_o_proprio_email_e_sincroniza_dados(): void
    {
        $fake = DirectoryEmulator::setup();

        LdapUser::create([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'department' => 'TI',
            'objectguid' => Str::orderedUuid(),
        ]);

        // Sem conta de serviço: o bind é feito com o próprio e-mail/senha do
        // usuário (primeiro formato tentado por ActiveDirectoryAuthenticator).
        $fake->getLdapConnection()->shouldAllowBindWith('fulano@cetem.gov.br');

        $response = $this->post('/login', [
            'email' => 'fulano@cetem.gov.br',
            'password' => 'senha-do-ad',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $usuario = User::where('email', 'fulano@cetem.gov.br')->first();

        $this->assertNotNull($usuario);
        $this->assertSame('Fulano da Silva', $usuario->name);
        $this->assertSame('TI', $usuario->ad_setor);
        $this->assertNotNull($usuario->ad_guid);
        $this->assertNotNull($usuario->ad_synced_at);
    }

    public function test_autentica_com_formato_down_level_quando_o_email_nao_e_aceito_como_upn(): void
    {
        $fake = DirectoryEmulator::setup();

        LdapUser::create([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'department' => 'TI',
            'objectguid' => Str::orderedUuid(),
        ]);

        $ldap = $fake->getLdapConnection();
        // O AD recusa o e-mail puro como identidade de bind (primeiro
        // formato tentado)...
        $ldap->expect(
            LdapFake::operation('bind')->with('fulano@cetem.gov.br', PHPUnit::anything())->andReturnErrorResponse()->once()
        );
        // ...mas aceita o formato down-level "NETBIOS\usuario" (segundo
        // formato tentado), igual ao usado pela integração do GLPI.
        $ldap->expect(
            LdapFake::operation('bind')->with('MINERAL\\fulano', PHPUnit::anything())->andReturnResponse()
        );

        $response = $this->post('/login', [
            'email' => 'fulano@cetem.gov.br',
            'password' => 'senha-do-ad',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_bind_recusado_em_todos_os_formatos_e_sem_conta_local_nao_autentica(): void
    {
        $fake = DirectoryEmulator::setup();
        $fake->getLdapConnection()->expect(
            LdapFake::operation('bind')->andReturnErrorResponse()
        );

        $response = $this->post('/login', [
            'email' => 'inexistente@cetem.gov.br',
            'password' => 'senha-qualquer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_administrado_so_na_intranet_continua_autenticando_por_fallback_local(): void
    {
        $fake = DirectoryEmulator::setup();
        $fake->getLdapConnection()->expect(
            LdapFake::operation('bind')->andReturnErrorResponse()
        );

        $usuario = User::factory()->create([
            'email' => 'manutencao@cetem.gov.br',
            'password' => bcrypt('senha-local'),
            'ad_guid' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'manutencao@cetem.gov.br',
            'password' => 'senha-local',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_usuario_ja_cadastrado_na_intranet_e_vinculado_ao_ad_pelo_email_no_primeiro_login(): void
    {
        $usuario = User::factory()->create([
            'email' => 'existente@cetem.gov.br',
            'ad_guid' => null,
        ]);

        $fake = DirectoryEmulator::setup();

        LdapUser::create([
            'cn' => 'Nome Atualizado Pelo AD',
            'mail' => 'existente@cetem.gov.br',
            'department' => 'ADM',
            'objectguid' => Str::orderedUuid(),
        ]);

        $fake->getLdapConnection()->shouldAllowBindWith('existente@cetem.gov.br');

        $response = $this->post('/login', [
            'email' => 'existente@cetem.gov.br',
            'password' => 'senha-do-ad',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertSame(1, User::where('email', 'existente@cetem.gov.br')->count());

        $usuario->refresh();
        $this->assertNotNull($usuario->ad_guid);
        $this->assertSame('ADM', $usuario->ad_setor);
    }
}
