<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Tests\TestCase;

class ActiveDirectoryAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        DirectoryEmulator::tearDown();

        parent::tearDown();
    }

    private function credenciais(string $email, string $password): array
    {
        return [
            'mail' => $email,
            'password' => $password,
            'fallback' => [
                'email' => $email,
                'password' => $password,
            ],
        ];
    }

    public function test_usuario_do_ad_autentica_via_bind_e_sincroniza_nome_email_e_setor(): void
    {
        $fake = DirectoryEmulator::setup();

        $ldapUser = LdapUser::create([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'department' => 'TI',
            'objectguid' => Str::orderedUuid(),
        ]);

        $fake->actingAs($ldapUser);

        $this->assertTrue(Auth::attempt($this->credenciais('fulano@cetem.gov.br', 'senha-do-ad')));

        $usuario = User::where('email', 'fulano@cetem.gov.br')->first();

        $this->assertNotNull($usuario);
        $this->assertSame('Fulano da Silva', $usuario->name);
        $this->assertSame('TI', $usuario->ad_setor);
        $this->assertNotNull($usuario->ad_guid);
        $this->assertNotNull($usuario->ad_synced_at);
    }

    public function test_bind_com_senha_incorreta_falha_mesmo_com_usuario_existente_no_ad(): void
    {
        $fake = DirectoryEmulator::setup();

        $ldapUser = LdapUser::create([
            'cn' => 'Fulano da Silva',
            'mail' => 'fulano@cetem.gov.br',
            'department' => 'TI',
            'objectguid' => Str::orderedUuid(),
        ]);

        // Não chama $fake->actingAs($ldapUser), então nenhum bind é aceito.
        $this->assertFalse(Auth::attempt($this->credenciais('fulano@cetem.gov.br', 'senha-errada')));
    }

    public function test_usuario_ja_cadastrado_na_intranet_e_vinculado_ao_ad_pelo_email_no_primeiro_login(): void
    {
        $usuario = User::factory()->create([
            'email' => 'existente@cetem.gov.br',
            'ad_guid' => null,
        ]);

        $fake = DirectoryEmulator::setup();

        $ldapUser = LdapUser::create([
            'cn' => 'Nome Atualizado Pelo AD',
            'mail' => 'existente@cetem.gov.br',
            'department' => 'ADM',
            'objectguid' => Str::orderedUuid(),
        ]);

        $fake->actingAs($ldapUser);

        $this->assertTrue(Auth::attempt($this->credenciais('existente@cetem.gov.br', 'senha-do-ad')));

        $this->assertSame(1, User::where('email', 'existente@cetem.gov.br')->count());

        $usuario->refresh();
        $this->assertNotNull($usuario->ad_guid);
        $this->assertSame('ADM', $usuario->ad_setor);
    }

    public function test_usuario_administrado_so_na_intranet_continua_autenticando_por_fallback_local(): void
    {
        DirectoryEmulator::setup();

        $usuario = User::factory()->create([
            'email' => 'manutencao@cetem.gov.br',
            'password' => bcrypt('senha-local'),
            'ad_guid' => null,
        ]);

        $this->assertTrue(Auth::attempt($this->credenciais('manutencao@cetem.gov.br', 'senha-local')));
        $this->assertAuthenticatedAs($usuario);
    }
}
