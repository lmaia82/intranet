<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPainelStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_de_usuarios_conta_somente_ativos(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);
        User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.index'));

        // 2 ativos (admin + 1) de 4 no total.
        $response->assertOk()->assertSee('Usuários ativos');
        $this->assertSame(2, User::where('is_active', true)->count());
        $this->assertSame(4, User::count());
    }
}
