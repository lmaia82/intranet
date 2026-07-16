<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OcrStatusEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_retorna_status_atual_sem_reconsultar_quando_ja_concluido(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['sigla' => 'TI']);
        $arquivo = Arquivo::create([
            'nome_original' => 'teste.pdf',
            'caminho' => 'uploads/teste.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
            'ocr_status' => 'concluido',
        ]);

        Http::fake();

        $this->actingAs($user)->getJson(route('repositorio.arquivos.ocr-status', $arquivo))
            ->assertOk()
            ->assertJson(['status' => 'concluido']);

        Http::assertNothingSent();
    }

    public function test_reconsulta_o_paperless_quando_pendente(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['sigla' => 'TI']);
        $arquivo = Arquivo::create([
            'nome_original' => 'teste.pdf',
            'caminho' => 'uploads/teste.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
            'ocr_status' => 'pendente',
            'paperless_task_id' => 'tarefa-uuid-endpoint',
        ]);

        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake([
            'paperless-teste/api/tasks/*' => Http::response([[
                'task_id' => 'tarefa-uuid-endpoint',
                'status' => 'FAILURE',
                'result' => 'Falha simulada.',
            ]], 200),
        ]);

        $this->actingAs($user)->getJson(route('repositorio.arquivos.ocr-status', $arquivo))
            ->assertOk()
            ->assertJson(['status' => 'falhou', 'erro' => 'Falha simulada.']);
    }

    public function test_bloqueia_acesso_a_arquivo_sem_permissao_de_visualizacao(): void
    {
        $sector = Sector::create(['sigla' => 'TI']);
        $outroSector = Sector::create(['sigla' => 'RH']);
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $arquivo = Arquivo::create([
            'nome_original' => 'restrito.pdf',
            'caminho' => 'uploads/restrito.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $outroSector->id,
            'is_private' => true,
            'ocr_status' => 'pendente',
        ]);

        $this->actingAs($user)->getJson(route('repositorio.arquivos.ocr-status', $arquivo))
            ->assertForbidden();
    }
}
