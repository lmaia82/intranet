<?php

namespace Tests\Feature;

use App\Models\Configuracao;
use App\Models\Tutorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutoriaisAtivoTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutoriais_desativado_bloqueia_a_pagina_mesmo_para_quem_tem_permissao(): void
    {
        Configuracao::atual()->update(['tutoriais_ativo' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('tutoriais.index'))->assertNotFound();
    }

    public function test_tutoriais_desativado_bloqueia_a_pagina_ate_para_admin(): void
    {
        Configuracao::atual()->update(['tutoriais_ativo' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('tutoriais.index'))->assertNotFound();
    }

    public function test_tutoriais_ativado_por_padrao_continua_acessivel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('tutoriais.index'))->assertOk();
    }

    public function test_link_de_tutoriais_some_do_menu_quando_desativado(): void
    {
        Configuracao::atual()->update(['tutoriais_ativo' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('Tutoriais');
    }

    public function test_widget_de_tutoriais_some_do_dashboard_quando_desativado(): void
    {
        Configuracao::atual()->update(['tutoriais_ativo' => false]);
        $user = User::factory()->create();
        Tutorial::create(['titulo' => 'Tutorial Widget X', 'data' => now(), 'youtube_url' => 'https://www.youtube.com/watch?v=abc123']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('Tutorial Widget X');
    }

    public function test_busca_nao_retorna_tutoriais_quando_desativado(): void
    {
        Configuracao::atual()->update(['tutoriais_ativo' => false]);
        $user = User::factory()->create();
        Tutorial::create(['titulo' => 'Tutorial Buscatermo Desativado', 'data' => now(), 'youtube_url' => 'https://www.youtube.com/watch?v=abc123']);

        $this->actingAs($user)->get(route('busca.index', ['q' => 'Buscatermo']))
            ->assertOk()
            ->assertDontSee('Tutorial Buscatermo Desativado');
    }
}
