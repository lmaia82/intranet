<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsuariosExcluirEmLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_exclui_usuarios_selecionados(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario1 = User::factory()->create();
        $usuario2 = User::factory()->create();
        $mantido = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.usuarios.destroy-lote'), [
            'ids' => [$usuario1->id, $usuario2->id],
        ]);

        $response->assertRedirect(route('admin.usuarios'));
        $this->assertModelMissing($usuario1);
        $this->assertModelMissing($usuario2);
        $this->assertModelExists($mantido);
    }

    public function test_exclusao_em_lote_ignora_o_proprio_id_do_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.usuarios.destroy-lote'), [
            'ids' => [$admin->id, $usuario->id],
        ]);

        $this->assertModelExists($admin);
        $this->assertModelMissing($usuario);
    }

    public function test_exclusao_em_lote_preserva_os_filtros_no_redirecionamento(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.usuarios.destroy-lote', [
            'nome' => 'algum-filtro',
        ]), [
            'ids' => [$usuario->id],
        ]);

        $response->assertRedirect(route('admin.usuarios', ['nome' => 'algum-filtro']));
    }

    public function test_ids_invalidos_falham_validacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.usuarios.destroy-lote'), [
            'ids' => [999999],
        ])->assertSessionHasErrors('ids.0');
    }

    public function test_usuario_nao_admin_nao_exclui_em_lote(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $usuario = User::factory()->create();

        $this->actingAs($user)->post(route('admin.usuarios.destroy-lote'), [
            'ids' => [$usuario->id],
        ])->assertForbidden();

        $this->assertModelExists($usuario);
    }

    public function test_tela_mostra_checkbox_para_selecionar_usuarios_menos_o_proprio(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $outro = User::factory()->create();
        Sector::create(['sigla' => 'TI']);

        $response = $this->actingAs($admin)->get(route('admin.usuarios'));

        $response->assertOk()
            ->assertSee('name="ids[]" value="' . $outro->id . '"', false)
            ->assertDontSee('name="ids[]" value="' . $admin->id . '"', false);
    }
}
