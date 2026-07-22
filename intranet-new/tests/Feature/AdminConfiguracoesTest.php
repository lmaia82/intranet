<?php

namespace Tests\Feature;

use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
