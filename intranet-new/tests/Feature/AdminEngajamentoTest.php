<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEngajamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_nao_admin_nao_acessa_engajamento(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.engajamento'))->assertForbidden();
    }

    public function test_admin_ve_estatisticas_de_engajamento(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $outroUsuario = User::factory()->create(['is_admin' => true]);

        Acesso::create(['user_id' => $admin->id, 'modulo' => 'ramais']);
        Acesso::create(['user_id' => $admin->id, 'modulo' => 'ramais']);
        Acesso::create(['user_id' => $outroUsuario->id, 'modulo' => 'informativos']);
        Acesso::create(['user_id' => $outroUsuario->id, 'modulo' => 'dashboard', 'created_at' => now()->subDays(20)]);

        $response = $this->actingAs($admin)->get(route('admin.engajamento'));

        $response->assertOk()
            ->assertSee('Usuários ativos hoje')
            ->assertSee('Ramais')
            ->assertSee('Informativos')
            // 2 usuários distintos com acesso hoje (admin via ramais, outroUsuario via informativos)
            ->assertSee('<p class="text-3xl font-bold text-blue-600">2</p>', false);
    }

    public function test_pagina_carrega_mesmo_sem_nenhum_acesso_registrado(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.engajamento'))
            ->assertOk()
            ->assertSee('Nenhum acesso registrado');
    }
}
