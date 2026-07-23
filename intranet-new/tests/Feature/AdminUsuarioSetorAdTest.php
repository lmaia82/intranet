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
}
