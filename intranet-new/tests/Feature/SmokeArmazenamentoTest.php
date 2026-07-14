<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeArmazenamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_telas_de_setores_e_armazenamento_renderizam(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sector::create(['name' => 'TI', 'quota_bytes' => 1048576]);

        $this->actingAs($admin)->get(route('admin.setores'))
            ->assertOk()->assertSee('Cota (MB)');

        $this->actingAs($admin)->get(route('admin.armazenamento'))
            ->assertOk()->assertSee('Armazenamento por Setor');

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()->assertSee('Armazenamento por Setor');
    }
}
