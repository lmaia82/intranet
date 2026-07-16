<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Services\PaperlessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaperlessWebhookController extends Controller
{
    public function __construct(private PaperlessService $paperless)
    {
    }

    public function handle(Request $request)
    {
        $segredoEsperado = (string) config('services.paperless.webhook_secret');
        $segredoRecebido = (string) $request->header('X-Webhook-Secret');

        abort_unless($segredoEsperado !== '' && hash_equals($segredoEsperado, $segredoRecebido), 403);

        $documentId = (int) $request->input('document_id');
        abort_if($documentId <= 0, 422, 'document_id inválido.');

        $documento = $this->paperless->buscarDocumento($documentId);

        if (!$documento) {
            return response()->json(['ok' => false, 'motivo' => 'documento não encontrado no paperless'], 200);
        }

        $arquivoId = $this->paperless->arquivoIdDoTitulo($documento['title'] ?? null);

        if (!$arquivoId) {
            return response()->json(['ok' => false, 'motivo' => 'título sem correlação com um arquivo'], 200);
        }

        $arquivo = Arquivo::find($arquivoId);

        if (!$arquivo) {
            return response()->json(['ok' => false, 'motivo' => 'arquivo não encontrado na intranet'], 200);
        }

        $arquivo->update([
            'paperless_document_id' => $documentId,
            'conteudo_ocr' => $documento['content'] ?? null,
            'ocr_status' => 'concluido',
        ]);

        $this->substituirPeloArquivoComOcr($arquivo, $documentId);

        return response()->json(['ok' => true]);
    }

    /**
     * Substitui o arquivo original no repositório pela versão processada
     * pelo paperless (mesmo conteúdo visual, mas com camada de texto do OCR
     * embutida, permitindo seleção/cópia de texto no PDF).
     */
    private function substituirPeloArquivoComOcr(Arquivo $arquivo, int $documentId): void
    {
        try {
            $pdfComOcr = $this->paperless->baixarPdfComOcr($documentId);

            if (!$pdfComOcr) {
                return;
            }

            Storage::disk('arquivos')->put($arquivo->caminho, $pdfComOcr);
            $arquivo->update(['tamanho' => strlen($pdfComOcr)]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao substituir arquivo pela versão com OCR do paperless-ngx', [
                'arquivo_id' => $arquivo->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
