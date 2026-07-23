<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSetoresCotaEmLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_atualiza_cota_dos_setores_selecionados(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor1 = Sector::create(['sigla' => 'TI', 'quota_bytes' => 1024]);
        $setor2 = Sector::create(['sigla' => 'RH']);

        $response = $this->actingAs($admin)->post(route('admin.setores.cota-lote'), [
            'ids' => [$setor1->id, $setor2->id],
            'nova_cota_mb' => 50,
        ]);

        $response->assertRedirect(route('admin.setores'));
        $this->assertSame(50 * 1048576, $setor1->fresh()->quota_bytes);
        $this->assertSame(50 * 1048576, $setor2->fresh()->quota_bytes);
    }

    public function test_cota_em_lote_vazia_remove_o_limite(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'TI', 'quota_bytes' => 1024]);

        $this->actingAs($admin)->post(route('admin.setores.cota-lote'), [
            'ids' => [$setor->id],
            'nova_cota_mb' => '',
        ]);

        $this->assertNull($setor->fresh()->quota_bytes);
    }

    public function test_nao_atualiza_setores_nao_selecionados(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $selecionado = Sector::create(['sigla' => 'TI']);
        $naoSelecionado = Sector::create(['sigla' => 'RH', 'quota_bytes' => 2048]);

        $this->actingAs($admin)->post(route('admin.setores.cota-lote'), [
            'ids' => [$selecionado->id],
            'nova_cota_mb' => 50,
        ]);

        $this->assertSame(50 * 1048576, $selecionado->fresh()->quota_bytes);
        $this->assertSame(2048, $naoSelecionado->fresh()->quota_bytes);
    }

    public function test_cota_invalida_falha_validacao(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'TI']);

        $this->actingAs($admin)->post(route('admin.setores.cota-lote'), [
            'ids' => [$setor->id],
            'nova_cota_mb' => -5,
        ])->assertSessionHasErrors('nova_cota_mb');
    }

    public function test_usuario_nao_admin_nao_atualiza_cota_em_lote(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $setor = Sector::create(['sigla' => 'TI']);

        $this->actingAs($user)->post(route('admin.setores.cota-lote'), [
            'ids' => [$setor->id],
            'nova_cota_mb' => 50,
        ])->assertForbidden();

        $this->assertNull($setor->fresh()->quota_bytes);
    }
}
