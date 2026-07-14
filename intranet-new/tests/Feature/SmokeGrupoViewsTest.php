<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeGrupoViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_telas_de_grupos_e_lote_renderizam(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grupo = Group::first();

        $this->actingAs($admin)->get(route('admin.grupos'))->assertOk()->assertSee('Colaboradores');
        $this->actingAs($admin)->get(route('admin.grupos.criar'))->assertOk()->assertSee('Permissões');
        $this->actingAs($admin)->get(route('admin.grupos.editar', $grupo))->assertOk();
        $this->actingAs($admin)->get(route('admin.usuarios'))->assertOk()->assertSee('Grupo');
        $this->actingAs($admin)->get(route('admin.usuarios.criar'))->assertOk()->assertSee('Grupo');
        $this->actingAs($admin)->get(route('admin.usuarios.lote.form'))->assertOk();
        $this->actingAs($admin)->get(route('admin.usuarios.grupo-lote.form'))->assertOk();
        $this->actingAs($admin)->get(route('admin.index'))->assertOk()->assertSee('Gerenciar Grupos');
    }

    public function test_publicacoes_aparece_no_menu_e_na_configuracao_de_grupos(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $grupo = Group::first();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()->assertSee('Publicações')->assertDontSee('Artigos');

        $this->actingAs($admin)->get(route('admin.grupos.editar', $grupo))
            ->assertOk()->assertSee('Publicações')->assertDontSee('Artigos');
    }
}
