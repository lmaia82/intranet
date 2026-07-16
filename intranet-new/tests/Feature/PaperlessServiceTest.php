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
        Http::fake(['paperless-teste/*' => Http::response(['task_id' => 'abc'], 200)]);

        $arquivo = $this->arquivoPdf();

        $enviado = app(PaperlessService::class)->enviarParaOcr($arquivo);

        $this->assertTrue($enviado);
        $this->assertEquals('pendente', $arquivo->fresh()->ocr_status);

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

    public function test_arquivo_id_do_titulo(): void
    {
        $service = app(PaperlessService::class);

        $this->assertEquals(42, $service->arquivoIdDoTitulo('intranet-arquivo-42'));
        $this->assertNull($service->arquivoIdDoTitulo('titulo-qualquer'));
        $this->assertNull($service->arquivoIdDoTitulo(null));
    }
}
