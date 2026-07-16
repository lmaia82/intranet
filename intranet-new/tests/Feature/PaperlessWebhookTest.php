<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaperlessWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function configurarPaperless(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Config::set('services.paperless.webhook_secret', 'segredo-teste');
    }

    private function arquivo(): Arquivo
    {
        $sector = Sector::create(['sigla' => 'TI']);

        return Arquivo::create([
            'nome_original' => 'teste.pdf',
            'caminho' => 'uploads/teste.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);
    }

    public function test_rejeita_sem_o_segredo_correto(): void
    {
        $this->configurarPaperless();

        $this->postJson(route('webhooks.paperless'), ['document_id' => 1])
            ->assertForbidden();
    }

    public function test_atualiza_arquivo_com_conteudo_ocr(): void
    {
        $this->configurarPaperless();
        $arquivo = $this->arquivo();

        Http::fake([
            'paperless-teste/api/documents/*' => Http::response([
                'id' => 99,
                'title' => "intranet-arquivo-{$arquivo->id}",
                'content' => 'Texto extraido via OCR de teste.',
            ], 200),
        ]);

        $this->postJson(route('webhooks.paperless'), ['document_id' => 99], [
            'X-Webhook-Secret' => 'segredo-teste',
        ])->assertOk()->assertJson(['ok' => true]);

        $arquivo->refresh();
        $this->assertEquals(99, $arquivo->paperless_document_id);
        $this->assertEquals('Texto extraido via OCR de teste.', $arquivo->conteudo_ocr);
    }

    public function test_nao_falha_quando_titulo_nao_correlaciona_com_arquivo(): void
    {
        $this->configurarPaperless();

        Http::fake([
            'paperless-teste/api/documents/*' => Http::response([
                'id' => 100,
                'title' => 'documento-qualquer-sem-padrao',
                'content' => 'texto',
            ], 200),
        ]);

        $this->postJson(route('webhooks.paperless'), ['document_id' => 100], [
            'X-Webhook-Secret' => 'segredo-teste',
        ])->assertOk()->assertJson(['ok' => false]);
    }
}
