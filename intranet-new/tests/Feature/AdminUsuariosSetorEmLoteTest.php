<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsuariosSetorEmLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_atualiza_setor_dos_usuarios_selecionados(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setorAntigo = Sector::create(['sigla' => 'RH']);
        $setorNovo = Sector::create(['sigla' => 'TI']);

        $usuario1 = User::factory()->create(['sector_id' => $setorAntigo->id]);
        $usuario2 = User::factory()->create(['sector_id' => null]);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.setor-lote'), [
            'ids' => [$usuario1->id, $usuario2->id],
            'novo_sector_id' => $setorNovo->id,
        ]);

        $response->assertRedirect(route('admin.usuarios'));
        $this->assertSame($setorNovo->id, $usuario1->fresh()->sector_id);
        $this->assertSame($setorNovo->id, $usuario2->fresh()->sector_id);
    }

    public function test_atualizar_setor_em_lote_para_vazio_remove_o_setor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'RH']);
        $usuario = User::factory()->create(['sector_id' => $setor->id]);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.setor-lote'), [
            'ids' => [$usuario->id],
            'novo_sector_id' => '',
        ]);

        $response->assertRedirect(route('admin.usuarios'));
        $this->assertNull($usuario->fresh()->sector_id);
    }

    public function test_atualizacao_em_lote_preserva_os_filtros_no_redirecionamento(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'TI']);
        $usuario = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.usuarios.setor-lote', [
            'nome' => 'algum-filtro',
        ]), [
            'ids' => [$usuario->id],
            'novo_sector_id' => $setor->id,
        ]);

        $response->assertRedirect(route('admin.usuarios', ['nome' => 'algum-filtro']));
    }

    public function test_setor_invalido_falha_validacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.usuarios.setor-lote'), [
            'ids' => [$usuario->id],
            'novo_sector_id' => 999999,
        ])->assertSessionHasErrors('novo_sector_id');
    }

    public function test_usuario_nao_admin_nao_atualiza_setor_em_lote(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $setor = Sector::create(['sigla' => 'TI']);
        $usuario = User::factory()->create();

        $this->actingAs($user)->post(route('admin.usuarios.setor-lote'), [
            'ids' => [$usuario->id],
            'novo_sector_id' => $setor->id,
        ])->assertForbidden();

        $this->assertNull($usuario->fresh()->sector_id);
    }
}
