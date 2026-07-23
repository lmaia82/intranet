<?php

namespace Tests\Feature;

use App\Models\MapeamentoSetorAd;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeParaSetoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_lista_setores_distintos_do_ad(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['ad_setor' => 'SEIN-ANTIGO']);
        User::factory()->create(['ad_setor' => 'SEIN-ANTIGO']);
        User::factory()->create(['ad_setor' => 'RH-ANTIGO']);

        $this->actingAs($admin)->get(route('admin.depara-setores'))
            ->assertOk()
            ->assertSeeInOrder(['RH-ANTIGO', 'SEIN-ANTIGO']);
    }

    public function test_admin_salva_mapeamento(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'SEIN']);
        User::factory()->create(['ad_setor' => 'SEIN-ANTIGO']);

        $this->actingAs($admin)->put(route('admin.depara-setores.update'), [
            'mapeamentos' => ['SEIN-ANTIGO' => $setor->id],
        ])->assertRedirect(route('admin.depara-setores'));

        $this->assertDatabaseHas('mapeamentos_setor_ad', [
            'ad_setor' => 'SEIN-ANTIGO',
            'sector_id' => $setor->id,
        ]);
    }

    public function test_salvar_mapeamento_em_branco_remove_mapeamento_existente(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'SEIN']);
        MapeamentoSetorAd::create(['ad_setor' => 'SEIN-ANTIGO', 'sector_id' => $setor->id]);

        $this->actingAs($admin)->put(route('admin.depara-setores.update'), [
            'mapeamentos' => ['SEIN-ANTIGO' => ''],
        ]);

        $this->assertDatabaseMissing('mapeamentos_setor_ad', ['ad_setor' => 'SEIN-ANTIGO']);
    }

    public function test_aplicar_preenche_apenas_usuarios_sem_setor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'SEIN']);
        $outroSetor = Sector::create(['sigla' => 'RH']);
        MapeamentoSetorAd::create(['ad_setor' => 'SEIN-ANTIGO', 'sector_id' => $setor->id]);

        $semSetor = User::factory()->create(['ad_setor' => 'SEIN-ANTIGO', 'sector_id' => null]);
        $comSetor = User::factory()->create(['ad_setor' => 'SEIN-ANTIGO', 'sector_id' => $outroSetor->id]);

        $this->actingAs($admin)->post(route('admin.depara-setores.aplicar'))
            ->assertRedirect(route('admin.depara-setores'));

        $this->assertEquals($setor->id, $semSetor->fresh()->sector_id);
        $this->assertEquals($outroSetor->id, $comSetor->fresh()->sector_id);
    }

    public function test_usuario_nao_admin_nao_acessa_de_para(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.depara-setores'))->assertForbidden();
        $this->actingAs($user)->put(route('admin.depara-setores.update'), [])->assertForbidden();
        $this->actingAs($user)->post(route('admin.depara-setores.aplicar'))->assertForbidden();
    }
}
