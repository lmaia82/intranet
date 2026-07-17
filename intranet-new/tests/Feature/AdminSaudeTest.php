<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Informativo;
use App\Models\InformativoEnvio;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminSaudeTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_nao_admin_nao_acessa_saude(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.saude'))->assertForbidden();
    }

    public function test_pagina_carrega_sem_dados(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.saude'))
            ->assertOk()
            ->assertSee('Nenhuma falha de envio')
            ->assertSee('Nenhum setor acima de 80%');
    }

    public function test_exibe_contagem_de_ocr_por_status_e_falhas(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $sector = Sector::create(['sigla' => 'TI']);

        Arquivo::create(['nome_original' => 'ok.pdf', 'caminho' => 'x1', 'extensao' => 'pdf', 'tamanho' => 1, 'sector_id' => $sector->id, 'is_private' => false, 'ocr_status' => 'concluido']);
        Arquivo::create(['nome_original' => 'proc.pdf', 'caminho' => 'x2', 'extensao' => 'pdf', 'tamanho' => 1, 'sector_id' => $sector->id, 'is_private' => false, 'ocr_status' => 'pendente']);
        Arquivo::create(['nome_original' => 'falhou.pdf', 'caminho' => 'x3', 'extensao' => 'pdf', 'tamanho' => 1, 'sector_id' => $sector->id, 'is_private' => false, 'ocr_status' => 'falhou', 'ocr_erro' => 'Documento duplicado.']);

        $response = $this->actingAs($admin)->get(route('admin.saude'));

        $response->assertOk()
            ->assertSee('falhou.pdf')
            ->assertSee('Documento duplicado.');
    }

    public function test_exibe_falhas_de_envio_de_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $informativo = Informativo::create(['title' => 'Aviso', 'content' => 'x', 'published_at' => now()]);

        InformativoEnvio::create(['informativo_id' => $informativo->id, 'email' => 'ok@cetem.gov.br', 'sucesso' => true, 'enviado_em' => now()]);
        InformativoEnvio::create(['informativo_id' => $informativo->id, 'email' => 'falha@cetem.gov.br', 'sucesso' => false, 'erro' => 'Conexão SMTP recusada', 'enviado_em' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.saude'));

        $response->assertOk()
            ->assertSee('falha@cetem.gov.br')
            ->assertSee('Conexão SMTP recusada');
    }

    public function test_exibe_setor_perto_da_cota(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $sector = Sector::create(['sigla' => 'QUASE-CHEIO', 'quota_bytes' => 1000]);
        Arquivo::create(['nome_original' => 'grande.pdf', 'caminho' => 'x', 'extensao' => 'pdf', 'tamanho' => 900, 'sector_id' => $sector->id, 'is_private' => false]);

        $this->actingAs($admin)->get(route('admin.saude'))
            ->assertOk()
            ->assertSee('QUASE-CHEIO');
    }

    public function test_indica_paperless_indisponivel_quando_nao_responde(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake(['paperless-teste/*' => Http::response('erro', 500)]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.saude'))
            ->assertOk()
            ->assertSee('Serviço indisponível');
    }
}
