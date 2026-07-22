<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsuarioSetorAdTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_mostra_setor_do_ad_e_indica_quando_confere_com_a_intranet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'TI']);

        $bateComAd = User::factory()->create(['ad_guid' => 'guid-1', 'ad_setor' => 'TI', 'sector_id' => $setor->id]);
        $naoBateComAd = User::factory()->create(['ad_guid' => 'guid-2', 'ad_setor' => 'RH', 'sector_id' => $setor->id]);
        $semAd = User::factory()->create(['ad_guid' => null, 'ad_setor' => null, 'sector_id' => $setor->id]);

        $response = $this->actingAs($admin)->get(route('admin.usuarios'));

        $response->assertOk()
            ->assertSee('Setor (AD)')
            ->assertSeeInOrder(['✅'])
            ->assertSeeInOrder(['⚠️'])
            ->assertSee('RH');

        $this->assertTrue($bateComAd->fresh()->setorBateComAd());
        $this->assertFalse($naoBateComAd->fresh()->setorBateComAd());
        $this->assertNull($semAd->fresh()->setorBateComAd());
    }

    public function test_botao_trazer_do_ad_so_aparece_quando_setor_da_intranet_esta_vazio(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sector::create(['sigla' => 'TI']);

        $semSetorIntranet = User::factory()->create(['ad_guid' => 'guid-1', 'ad_setor' => 'TI', 'sector_id' => null]);
        User::factory()->create(['ad_guid' => 'guid-2', 'ad_setor' => 'TI', 'sector_id' => Sector::first()->id]);

        $response = $this->actingAs($admin)->get(route('admin.usuarios'));

        $response->assertOk()->assertSee(route('admin.usuarios.setor.trazer-do-ad', $semSetorIntranet), false);
    }

    public function test_trazer_do_ad_preenche_setor_quando_sigla_corresponde(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setor = Sector::create(['sigla' => 'TI']);
        $usuario = User::factory()->create(['ad_guid' => 'guid-1', 'ad_setor' => 'TI', 'sector_id' => null]);

        $this->actingAs($admin)->post(route('admin.usuarios.setor.trazer-do-ad', $usuario))
            ->assertRedirect(route('admin.usuarios'));

        $this->assertSame($setor->id, $usuario->fresh()->sector_id);
    }

    public function test_trazer_do_ad_nao_altera_nada_quando_sigla_nao_corresponde_a_setor_algum(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $usuario = User::factory()->create(['ad_guid' => 'guid-1', 'ad_setor' => 'INEXISTENTE', 'sector_id' => null]);

        $this->actingAs($admin)->post(route('admin.usuarios.setor.trazer-do-ad', $usuario))
            ->assertRedirect(route('admin.usuarios'));

        $this->assertNull($usuario->fresh()->sector_id);
    }

    public function test_trazer_do_ad_nao_sobrescreve_setor_ja_definido_na_intranet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setorIntranet = Sector::create(['sigla' => 'RH']);
        Sector::create(['sigla' => 'TI']);
        $usuario = User::factory()->create(['ad_guid' => 'guid-1', 'ad_setor' => 'TI', 'sector_id' => $setorIntranet->id]);

        $this->actingAs($admin)->post(route('admin.usuarios.setor.trazer-do-ad', $usuario))
            ->assertRedirect(route('admin.usuarios'));

        $this->assertSame($setorIntranet->id, $usuario->fresh()->sector_id);
    }

    public function test_usuario_nao_admin_nao_traz_setor_do_ad(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $usuario = User::factory()->create(['ad_guid' => 'guid-1', 'ad_setor' => 'TI', 'sector_id' => null]);

        $this->actingAs($user)->post(route('admin.usuarios.setor.trazer-do-ad', $usuario))->assertForbidden();
    }
}
