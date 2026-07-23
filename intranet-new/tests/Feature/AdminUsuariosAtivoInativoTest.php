<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsuariosAtivoInativoTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtra_por_dominio_de_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['name' => 'Interno Cetem', 'email' => 'interno@cetem.gov.br']);
        User::factory()->create(['name' => 'Externo Gmail', 'email' => 'externo@gmail.com']);

        $this->actingAs($admin)->get(route('admin.usuarios', ['dominio_email' => 'cetem']))
            ->assertOk()->assertSee('Interno Cetem')->assertDontSee('Externo Gmail');

        $this->actingAs($admin)->get(route('admin.usuarios', ['dominio_email' => 'externo']))
            ->assertOk()->assertSee('Externo Gmail')->assertDontSee('Interno Cetem');
    }

    public function test_filtra_por_status_ativo_inativo(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['name' => 'Usuario Ativo', 'is_active' => true]);
        User::factory()->create(['name' => 'Usuario Inativo', 'is_active' => false]);

        $this->actingAs($admin)->get(route('admin.usuarios', ['is_active' => '1']))
            ->assertOk()->assertSee('Usuario Ativo')->assertDontSee('Usuario Inativo');

        $this->actingAs($admin)->get(route('admin.usuarios', ['is_active' => '0']))
            ->assertOk()->assertSee('Usuario Inativo')->assertDontSee('Usuario Ativo');
    }

    public function test_admin_desativa_e_reativa_usuario_individualmente(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.usuarios.toggle-ativo', $usuario))
            ->assertRedirect(route('admin.usuarios'));
        $this->assertFalse($usuario->fresh()->is_active);

        $this->actingAs($admin)->post(route('admin.usuarios.toggle-ativo', $usuario))
            ->assertRedirect(route('admin.usuarios'));
        $this->assertTrue($usuario->fresh()->is_active);
    }

    public function test_admin_nao_pode_desativar_a_si_mesmo(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.usuarios.toggle-ativo', $admin))->assertForbidden();
    }

    public function test_desativar_em_lote_ignora_o_proprio_id_do_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.usuarios.desativar-lote'), [
            'ids' => [$admin->id, $usuario->id],
        ]);

        $this->assertTrue($admin->fresh()->is_active);
        $this->assertFalse($usuario->fresh()->is_active);
    }

    public function test_ativar_em_lote(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario1 = User::factory()->create(['is_active' => false]);
        $usuario2 = User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.ativar-lote'), [
            'ids' => [$usuario1->id, $usuario2->id],
        ]);

        $response->assertRedirect(route('admin.usuarios'));
        $this->assertTrue($usuario1->fresh()->is_active);
        $this->assertTrue($usuario2->fresh()->is_active);
    }

    public function test_usuario_desativado_nao_consegue_logar_pelo_fallback_local(): void
    {
        $usuario = User::factory()->create([
            'email' => 'inativo@cetem.gov.br',
            'password' => bcrypt('senha-local'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inativo@cetem.gov.br',
            'password' => 'senha-local',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_nao_admin_nao_desativa(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $usuario = User::factory()->create();

        $this->actingAs($user)->post(route('admin.usuarios.toggle-ativo', $usuario))->assertForbidden();
        $this->actingAs($user)->post(route('admin.usuarios.desativar-lote'), ['ids' => [$usuario->id]])->assertForbidden();
        $this->actingAs($user)->post(route('admin.usuarios.ativar-lote'), ['ids' => [$usuario->id]])->assertForbidden();
    }
}
