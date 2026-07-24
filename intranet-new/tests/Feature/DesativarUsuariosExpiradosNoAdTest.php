<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesativarUsuariosExpiradosNoAdTest extends TestCase
{
    use RefreshDatabase;

    public function test_desativa_usuario_ativo_com_expiracao_no_ad_ja_passada(): void
    {
        $usuario = User::factory()->create([
            'is_active' => true,
            'ad_expira_em' => now()->subDay(),
        ]);

        $this->artisan('usuarios:desativar-expirados-ad')->assertSuccessful();

        $this->assertFalse($usuario->fresh()->is_active);
    }

    public function test_nao_desativa_usuario_com_expiracao_no_ad_ainda_no_futuro(): void
    {
        $usuario = User::factory()->create([
            'is_active' => true,
            'ad_expira_em' => now()->addDay(),
        ]);

        $this->artisan('usuarios:desativar-expirados-ad');

        $this->assertTrue($usuario->fresh()->is_active);
    }

    public function test_nao_desativa_usuario_sem_data_de_expiracao_no_ad(): void
    {
        $usuario = User::factory()->create([
            'is_active' => true,
            'ad_expira_em' => null,
        ]);

        $this->artisan('usuarios:desativar-expirados-ad');

        $this->assertTrue($usuario->fresh()->is_active);
    }

    public function test_nao_falha_ao_reprocessar_usuario_ja_inativo(): void
    {
        $usuario = User::factory()->create([
            'is_active' => false,
            'ad_expira_em' => now()->subMonth(),
        ]);

        $this->artisan('usuarios:desativar-expirados-ad')->assertSuccessful();

        $this->assertFalse($usuario->fresh()->is_active);
    }
}
