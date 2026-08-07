<?php

namespace Tests\Feature\Auth;

use App\Models\Group;
use App\Models\Sector;
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

    /**
     * O AD do CETEM representa o setor como a OU do usuário (ex.:
     * OU=TI,OU=CETEM,DC=mineral,DC=cetem), não como um atributo — ver
     * App\Services\ActiveDirectorySetorHydrator.
     */
    private function criarUsuarioNoAd(array $atributos, string $setor): LdapUser
    {
        $usuario = new LdapUser($atributos);
        $usuario->inside("OU={$setor},OU=CETEM,DC=mineral,DC=cetem");
        $usuario->save();

        return $usuario;
    }

    public function test_usuario_do_ad_autentica_via_bind_direto_com_o_proprio_email_e_sincroniza_dados(): void
    {
        $setor = Sector::create(['sigla' => 'TI']);
        Group::create(['name' => 'Leitores']);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'samaccountname' => 'fulano',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'TI');

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

        // Primeiro login: setor importado automaticamente do AD e grupo
        // "Leitores" (mínimo privilégio) atribuído por padrão.
        $this->assertSame($setor->id, $usuario->sector_id);
        $this->assertSame('Leitores', $usuario->group->name);
    }

    public function test_usuario_do_ad_autentica_so_com_o_login_sem_dominio(): void
    {
        // Padrão de rede do CETEM: digita só "fulano", não
        // "fulano@cetem.gov.br" — o UPN completo é montado por dentro
        // (config('ldap.upn_domain')) pro bind, e a busca no AD depois é
        // por samaccountname, não mail.
        Group::create(['name' => 'Leitores']);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'samaccountname' => 'fulano',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('fulano@cetem.gov.br');

        $response = $this->post('/login', [
            'email' => 'fulano',
            'password' => 'senha-do-ad',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $usuario = User::where('email', 'fulano@cetem.gov.br')->first();
        $this->assertNotNull($usuario);
        $this->assertSame('Fulano da Silva', $usuario->name);
    }

    public function test_primeiro_login_entra_no_grupo_leitor_mesmo_quando_setor_do_ad_nao_corresponde_a_nenhum_cadastrado(): void
    {
        Group::create(['name' => 'Leitores']);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'samaccountname' => 'fulano',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'SETOR-INEXISTENTE-NA-INTRANET');

        $fake->getLdapConnection()->shouldAllowBindWith('fulano@cetem.gov.br');

        $this->post('/login', [
            'email' => 'fulano@cetem.gov.br',
            'password' => 'senha-do-ad',
        ]);

        $usuario = User::where('email', 'fulano@cetem.gov.br')->first();

        $this->assertNull($usuario->sector_id);
        $this->assertSame('Leitores', $usuario->group->name);
    }

    public function test_primeiro_login_nao_falha_quando_grupo_leitores_ainda_nao_existe(): void
    {
        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'samaccountname' => 'fulano',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('fulano@cetem.gov.br');

        $this->post('/login', [
            'email' => 'fulano@cetem.gov.br',
            'password' => 'senha-do-ad',
        ]);

        $usuario = User::where('email', 'fulano@cetem.gov.br')->first();

        $this->assertNotNull($usuario);
        $this->assertNull($usuario->group_id);
    }

    public function test_autentica_com_formato_down_level_quando_o_email_nao_e_aceito_como_upn(): void
    {
        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'samaccountname' => 'fulano',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'TI');

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

    public function test_usuario_administrado_so_na_intranet_autentica_so_com_o_login_sem_dominio(): void
    {
        // Mesmo fallback local, mas digitando só "manutencao" — acha o
        // e-mail completo pela parte antes do "@" (LoginRequest::resolverEmailLocal).
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
            'email' => 'manutencao',
            'password' => 'senha-local',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_usuario_ja_cadastrado_na_intranet_e_vinculado_ao_ad_pelo_email_no_primeiro_login(): void
    {
        $setorJaDefinido = Sector::create(['sigla' => 'RH']);
        $grupoJaDefinido = Group::create(['name' => 'Administradores']);

        $usuario = User::factory()->create([
            'email' => 'existente@cetem.gov.br',
            'ad_guid' => null,
            'sector_id' => $setorJaDefinido->id,
            'group_id' => $grupoJaDefinido->id,
        ]);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Nome Atualizado Pelo AD',
            'mail' => 'existente@cetem.gov.br',
            'samaccountname' => 'existente',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'ADM');

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

        // Usuário já existia (vínculo por e-mail, não é o primeiro login) —
        // setor/grupo definidos manualmente pelo admin não são sobrescritos.
        $this->assertSame($setorJaDefinido->id, $usuario->sector_id);
        $this->assertSame($grupoJaDefinido->id, $usuario->group_id);
    }

    public function test_usuario_desativado_nao_autentica_mesmo_com_senha_correta_no_ad(): void
    {
        $usuario = User::factory()->create([
            'email' => 'desativado@cetem.gov.br',
            'is_active' => false,
        ]);

        $fake = DirectoryEmulator::setup();

        $this->criarUsuarioNoAd([
            'cn' => 'Fulano Desativado',
            'mail' => 'desativado@cetem.gov.br',
            'samaccountname' => 'desativado',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('desativado@cetem.gov.br');

        $response = $this->post('/login', [
            'email' => 'desativado@cetem.gov.br',
            'password' => 'senha-do-ad',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_ad_com_objeto_duplicado_da_migracao_reaproveita_a_conta_existente_em_vez_de_duplicar(): void
    {
        // Cenário real observado em produção: o AD (em migração) tem dois
        // objetos pro mesmo sAMAccountName — um com "mail" correto (já
        // vinculado à conta certa na intranet) e outro, mais novo/GUID
        // diferente, com "mail" incompleto (só o usuário, sem "@dominio").
        // A busca por samaccountname pode achar esse segundo — sem a
        // recuperação, isso criava um usuário fantasma sem privilégios.
        Group::create(['name' => 'Leitores']);

        $usuarioExistente = User::factory()->create([
            'email' => 'fulano@cetem.gov.br',
            'is_admin' => true,
            'ad_guid' => (string) Str::orderedUuid(),
        ]);

        $fake = DirectoryEmulator::setup();

        // Objeto "duplicado" com mail quebrado — é o que a busca por
        // samaccountname acha dessa vez (GUID diferente do já vinculado).
        $this->criarUsuarioNoAd([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano',
            'samaccountname' => 'fulano',
            'objectguid' => Str::orderedUuid(),
        ], setor: 'TI');

        $fake->getLdapConnection()->shouldAllowBindWith('MINERAL\\fulano');

        $response = $this->post('/login', [
            'email' => 'fulano',
            'password' => 'senha-do-ad',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($usuarioExistente);

        // Não duplicou, e o admin da conta original não foi perdido.
        $this->assertSame(1, User::where('email', 'fulano@cetem.gov.br')->count());
        $this->assertTrue($usuarioExistente->fresh()->is_admin);
    }
}
