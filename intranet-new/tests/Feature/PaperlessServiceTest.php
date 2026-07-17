<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Sector;
use App\Services\PaperlessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaperlessServiceTest extends TestCase
{
    use RefreshDatabase;

    private function arquivoPdf(): Arquivo
    {
        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        Storage::disk('arquivos')->put('uploads/teste.pdf', '%PDF-1.4 conteudo de teste');

        return Arquivo::create([
            'nome_original' => 'teste.pdf',
            'caminho' => 'uploads/teste.pdf',
            'extensao' => 'pdf',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);
    }

    public function test_envia_pdf_para_o_paperless_quando_configurado(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake(['paperless-teste/*' => Http::response('"tarefa-uuid-123"', 200)]);

        $arquivo = $this->arquivoPdf();

        $enviado = app(PaperlessService::class)->enviarParaOcr($arquivo);

        $this->assertTrue($enviado);
        $arquivo->refresh();
        $this->assertEquals('pendente', $arquivo->ocr_status);
        $this->assertEquals('tarefa-uuid-123', $arquivo->paperless_task_id);

        Http::assertSent(function ($request) use ($arquivo) {
            return str_contains($request->url(), 'post_document')
                && $request->hasHeader('Authorization', 'Token token-teste')
                && str_contains($request->body(), "intranet-arquivo-{$arquivo->id}");
        });
    }

    public function test_nao_envia_arquivo_que_nao_e_pdf(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake();

        Storage::fake('arquivos');
        $sector = Sector::create(['sigla' => 'TI']);
        $arquivo = Arquivo::create([
            'nome_original' => 'planilha.xlsx',
            'caminho' => 'uploads/planilha.xlsx',
            'extensao' => 'xlsx',
            'tamanho' => 100,
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);

        app(PaperlessService::class)->enviarParaOcr($arquivo);

        Http::assertNothingSent();
    }

    public function test_nao_falha_quando_paperless_nao_esta_configurado(): void
    {
        Config::set('services.paperless.internal_url', null);
        Config::set('services.paperless.token', null);
        Http::fake();

        $arquivo = $this->arquivoPdf();

        app(PaperlessService::class)->enviarParaOcr($arquivo);

        Http::assertNothingSent();
    }

    public function test_nao_falha_quando_paperless_esta_indisponivel(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake(['paperless-teste/*' => Http::response('erro', 500)]);

        $arquivo = $this->arquivoPdf();

        // Não deve lançar exceção mesmo com falha na chamada.
        $enviado = app(PaperlessService::class)->enviarParaOcr($arquivo);

        $this->assertFalse($enviado);
        $this->assertEquals('falhou', $arquivo->fresh()->ocr_status);
    }

    public function test_sincronizarPendente_marca_falha_quando_paperless_rejeita(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');

        $arquivo = $this->arquivoPdf();
        $arquivo->update(['ocr_status' => 'pendente', 'paperless_task_id' => 'tarefa-uuid-falha']);

        Http::fake([
            'paperless-teste/api/tasks/*' => Http::response([[
                'task_id' => 'tarefa-uuid-falha',
                'status' => 'FAILURE',
                'result' => 'Documento duplicado.',
            ]], 200),
        ]);

        app(PaperlessService::class)->sincronizarPendente($arquivo);

        $arquivo->refresh();
        $this->assertEquals('falhou', $arquivo->ocr_status);
        $this->assertEquals('Documento duplicado.', $arquivo->ocr_erro);
    }

    public function test_sincronizarPendente_aplica_resultado_quando_webhook_nao_chegou(): void
    {
        Storage::fake('arquivos');
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');

        $arquivo = $this->arquivoPdf();
        $arquivo->update(['ocr_status' => 'pendente', 'paperless_task_id' => 'tarefa-uuid-sucesso']);

        Http::fake([
            'paperless-teste/api/tasks/*' => Http::response([[
                'task_id' => 'tarefa-uuid-sucesso',
                'status' => 'SUCCESS',
                'related_document' => '7',
            ]], 200),
            'paperless-teste/api/documents/*/download/*' => Http::response('%PDF-1.4 com ocr', 200),
            'paperless-teste/api/documents/*' => Http::response([
                'id' => 7,
                'title' => "intranet-arquivo-{$arquivo->id}",
                'content' => 'Conteudo recuperado via fallback.',
            ], 200),
        ]);

        app(PaperlessService::class)->sincronizarPendente($arquivo);

        $arquivo->refresh();
        $this->assertEquals('concluido', $arquivo->ocr_status);
        $this->assertEquals(7, $arquivo->paperless_document_id);
        $this->assertEquals('Conteudo recuperado via fallback.', $arquivo->conteudo_ocr);
    }

    public function test_sincronizarPendente_nao_faz_nada_sem_task_id(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake();

        $arquivo = $this->arquivoPdf();
        $arquivo->update(['ocr_status' => 'pendente']);

        app(PaperlessService::class)->sincronizarPendente($arquivo);

        Http::assertNothingSent();
        $this->assertEquals('pendente', $arquivo->fresh()->ocr_status);
    }

    public function test_estaDisponivel_retorna_true_quando_paperless_responde_ok(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake(['paperless-teste/*' => Http::response(['results' => []], 200)]);

        $this->assertTrue(app(PaperlessService::class)->estaDisponivel());
    }

    public function test_estaDisponivel_retorna_false_quando_paperless_falha(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake(['paperless-teste/*' => Http::response('erro', 500)]);

        $this->assertFalse(app(PaperlessService::class)->estaDisponivel());
    }

    public function test_estaDisponivel_retorna_false_quando_nao_configurado(): void
    {
        Config::set('services.paperless.internal_url', null);
        Config::set('services.paperless.token', null);

        $this->assertFalse(app(PaperlessService::class)->estaDisponivel());
    }

    public function test_arquivo_id_do_titulo(): void
    {
        $service = app(PaperlessService::class);

        $this->assertEquals(42, $service->arquivoIdDoTitulo('intranet-arquivo-42'));
        $this->assertNull($service->arquivoIdDoTitulo('titulo-qualquer'));
        $this->assertNull($service->arquivoIdDoTitulo(null));
    }
}
