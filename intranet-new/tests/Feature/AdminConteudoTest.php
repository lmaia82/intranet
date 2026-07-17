<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\Arquivo;
use App\Models\Informativo;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConteudoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_nao_admin_nao_acessa_conteudo(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.conteudo'))->assertForbidden();
    }

    public function test_pagina_carrega_sem_dados(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.conteudo'))
            ->assertOk()
            ->assertSee('Nenhuma leitura registrada')
            ->assertSee('Nenhum download registrado')
            ->assertSee('Nenhuma busca sem resultado');
    }

    public function test_ranking_de_informativos_arquivos_e_buscas(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $sector = Sector::create(['sigla' => 'TI']);

        $informativo = Informativo::create(['title' => 'Informativo Popular', 'content' => 'x', 'published_at' => now()]);
        $arquivo = Arquivo::create([
            'nome_original' => 'arquivo-popular.pdf',
            'caminho' => 'uploads/arquivo-popular.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);

        Acesso::create(['user_id' => $admin->id, 'modulo' => 'informativos', 'referencia_tipo' => 'informativo', 'referencia_id' => $informativo->id]);
        Acesso::create(['user_id' => $admin->id, 'modulo' => 'informativos', 'referencia_tipo' => 'informativo', 'referencia_id' => $informativo->id]);
        Acesso::create(['user_id' => $admin->id, 'modulo' => 'repositorio', 'referencia_tipo' => 'arquivo', 'referencia_id' => $arquivo->id]);
        Acesso::create(['user_id' => $admin->id, 'modulo' => 'busca', 'termo' => 'termo popular', 'resultados' => 3]);
        Acesso::create(['user_id' => $admin->id, 'modulo' => 'busca', 'termo' => 'termo popular', 'resultados' => 3]);
        Acesso::create(['user_id' => $admin->id, 'modulo' => 'busca', 'termo' => 'lacuna de conteudo', 'resultados' => 0]);

        $response = $this->actingAs($admin)->get(route('admin.conteudo'));

        $response->assertOk()
            ->assertSee('Informativo Popular')
            ->assertSee('arquivo-popular.pdf')
            ->assertSee('termo popular')
            ->assertSee('lacuna de conteudo');
    }
}
