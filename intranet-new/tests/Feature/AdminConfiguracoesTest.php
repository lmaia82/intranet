<?php

namespace Tests\Feature;

use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminConfiguracoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_nao_admin_nao_acessa_configuracoes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.configuracoes'))->assertForbidden();
    }

    public function test_pagina_mostra_previa_de_login_desativada_por_padrao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.configuracoes'))
            ->assertOk()
            ->assertSee('Desativada');
    }

    public function test_toggle_ativa_e_desativa_previa_de_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertFalse(Configuracao::atual()->previa_login_ativa);

        $this->actingAs($admin)->post(route('admin.configuracoes.previa-login'))
            ->assertRedirect(route('admin.configuracoes'));

        $this->assertTrue(Configuracao::atual()->previa_login_ativa);

        $this->actingAs($admin)->post(route('admin.configuracoes.previa-login'));

        $this->assertFalse(Configuracao::atual()->previa_login_ativa);
    }

    public function test_usuario_nao_admin_nao_altera_previa_de_login(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.configuracoes.previa-login'))->assertForbidden();

        $this->assertFalse(Configuracao::atual()->previa_login_ativa);
    }

    public function test_pagina_mostra_tempo_de_inatividade_padrao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.configuracoes'))
            ->assertOk()
            ->assertSee('value="120"', false);
    }

    public function test_admin_atualiza_o_tempo_de_inatividade(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.configuracoes.tempo-inatividade'), [
            'tempo_inatividade_minutos' => 30,
        ])->assertRedirect(route('admin.configuracoes'));

        $this->assertSame(30, Configuracao::atual()->tempo_inatividade_minutos);
    }

    public function test_tempo_de_inatividade_invalido_falha_validacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.configuracoes.tempo-inatividade'), [
            'tempo_inatividade_minutos' => 0,
        ])->assertSessionHasErrors('tempo_inatividade_minutos');

        $this->actingAs($admin)->post(route('admin.configuracoes.tempo-inatividade'), [
            'tempo_inatividade_minutos' => 'abc',
        ])->assertSessionHasErrors('tempo_inatividade_minutos');

        $this->assertSame(120, Configuracao::atual()->tempo_inatividade_minutos);
    }

    public function test_usuario_nao_admin_nao_altera_tempo_de_inatividade(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.configuracoes.tempo-inatividade'), [
            'tempo_inatividade_minutos' => 30,
        ])->assertForbidden();

        $this->assertSame(120, Configuracao::atual()->tempo_inatividade_minutos);
    }

    public function test_valor_configurado_e_aplicado_ao_tempo_de_vida_da_sessao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Configuracao::atual()->update(['tempo_inatividade_minutos' => 45]);

        $this->actingAs($admin)->get(route('admin.configuracoes'));

        $this->assertSame(45, config('session.lifetime'));
    }

    public function test_pagina_mostra_aba_tutoriais_ativada_por_padrao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.configuracoes'))
            ->assertOk()
            ->assertSee('Aba Tutoriais');

        $this->assertTrue(Configuracao::atual()->tutoriais_ativo);
    }

    public function test_toggle_ativa_e_desativa_aba_tutoriais(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.configuracoes.tutoriais'))
            ->assertRedirect(route('admin.configuracoes'));

        $this->assertFalse(Configuracao::atual()->tutoriais_ativo);

        $this->actingAs($admin)->post(route('admin.configuracoes.tutoriais'));

        $this->assertTrue(Configuracao::atual()->tutoriais_ativo);
    }

    public function test_usuario_nao_admin_nao_altera_aba_tutoriais(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.configuracoes.tutoriais'))->assertForbidden();

        $this->assertTrue(Configuracao::atual()->tutoriais_ativo);
    }

    public function test_pagina_mostra_configuracoes_de_sso_padrao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.configuracoes'))
            ->assertOk()
            ->assertSee('Sessão do SSO')
            ->assertSee('value="30"', false)
            ->assertSee('value="8"', false);

        $this->assertTrue(Configuracao::atual()->sso_exigir_login_ao_fechar_navegador);
    }

    public function test_admin_atualiza_configuracoes_de_sso_e_sincroniza_com_o_keycloak(): void
    {
        Http::fake([
            '*/protocol/openid-connect/token' => Http::response(['access_token' => 'token-de-teste']),
            '*/admin/realms/*' => Http::response(null, 204),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.configuracoes.sso'), [
            'sso_inatividade_minutos' => 15,
            'sso_duracao_maxima_horas' => 4,
            'sso_exigir_login_ao_fechar_navegador' => '1',
        ])->assertRedirect(route('admin.configuracoes'));

        $configuracao = Configuracao::atual();
        $this->assertSame(15, $configuracao->sso_inatividade_minutos);
        $this->assertSame(4, $configuracao->sso_duracao_maxima_horas);
        $this->assertTrue($configuracao->sso_exigir_login_ao_fechar_navegador);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/admin/realms/')
                && $request['ssoSessionIdleTimeout'] === 15 * 60
                && $request['ssoSessionMaxLifespan'] === 4 * 3600
                && $request['rememberMe'] === false;
        });
    }

    public function test_desmarcar_exigir_login_ao_fechar_navegador_libera_lembrar_se_no_keycloak(): void
    {
        Http::fake([
            '*/protocol/openid-connect/token' => Http::response(['access_token' => 'token-de-teste']),
            '*/admin/realms/*' => Http::response(null, 204),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.configuracoes.sso'), [
            'sso_inatividade_minutos' => 30,
            'sso_duracao_maxima_horas' => 8,
            // Checkbox desmarcado: nem envia o campo.
        ]);

        $this->assertFalse(Configuracao::atual()->sso_exigir_login_ao_fechar_navegador);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/admin/realms/') && $request['rememberMe'] === true);
    }

    public function test_configuracoes_de_sso_invalidas_falham_validacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.configuracoes.sso'), [
            'sso_inatividade_minutos' => 0,
            'sso_duracao_maxima_horas' => 999,
        ])->assertSessionHasErrors(['sso_inatividade_minutos', 'sso_duracao_maxima_horas']);

        $this->assertSame(30, Configuracao::atual()->sso_inatividade_minutos);
    }

    public function test_usuario_nao_admin_nao_altera_configuracoes_de_sso(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.configuracoes.sso'), [
            'sso_inatividade_minutos' => 15,
            'sso_duracao_maxima_horas' => 4,
        ])->assertForbidden();

        $this->assertSame(30, Configuracao::atual()->sso_inatividade_minutos);
    }
}
