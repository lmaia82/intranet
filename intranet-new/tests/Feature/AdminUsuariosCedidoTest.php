<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsuariosCedidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_marcar_como_cedido_desativa_o_usuario_automaticamente(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['cedido' => false, 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.toggle-cedido', $usuario));

        $response->assertRedirect(route('admin.usuarios'));

        $usuario->refresh();
        $this->assertTrue($usuario->cedido);
        $this->assertFalse($usuario->is_active);
    }

    public function test_desmarcar_cedido_nao_reativa_automaticamente(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['cedido' => true, 'is_active' => false]);

        $this->actingAs($admin)->post(route('admin.usuarios.toggle-cedido', $usuario));

        $usuario->refresh();
        $this->assertFalse($usuario->cedido);
        $this->assertFalse($usuario->is_active);
    }

    public function test_admin_nao_pode_marcar_a_si_mesmo_como_cedido(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.usuarios.toggle-cedido', $admin))->assertForbidden();
    }

    public function test_marcar_cedidos_em_lote_desativa_os_selecionados(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $u1 = User::factory()->create(['cedido' => false, 'is_active' => true]);
        $u2 = User::factory()->create(['cedido' => false, 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.cedido-lote'), [
            'ids' => [$u1->id, $u2->id],
        ]);

        $response->assertRedirect(route('admin.usuarios'));

        $this->assertTrue($u1->fresh()->cedido);
        $this->assertFalse($u1->fresh()->is_active);
        $this->assertTrue($u2->fresh()->cedido);
        $this->assertFalse($u2->fresh()->is_active);
    }

    public function test_marcar_cedidos_em_lote_ignora_o_proprio_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'cedido' => false, 'is_active' => true]);
        $outro = User::factory()->create(['cedido' => false, 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.usuarios.cedido-lote'), [
            'ids' => [$admin->id, $outro->id],
        ]);

        $this->assertFalse($admin->fresh()->cedido);
        $this->assertTrue($admin->fresh()->is_active);
        $this->assertTrue($outro->fresh()->cedido);
    }

    public function test_filtra_usuarios_cedidos(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['name' => 'Cedido Um', 'cedido' => true, 'is_active' => false]);
        User::factory()->create(['name' => 'Nao Cedido', 'cedido' => false]);

        $response = $this->actingAs($admin)->get(route('admin.usuarios', ['cedido' => '1', 'is_active' => '']));

        $response->assertOk()->assertSee('Cedido Um')->assertDontSee('Nao Cedido');
    }

    public function test_usuario_nao_admin_nao_marca_cedido(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $outro = User::factory()->create();

        $this->actingAs($user)->post(route('admin.usuarios.toggle-cedido', $outro))->assertForbidden();
        $this->actingAs($user)->post(route('admin.usuarios.cedido-lote'), ['ids' => [$outro->id]])->assertForbidden();
    }
}
