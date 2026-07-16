<?php

namespace App\Services;

use App\Models\Arquivo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaperlessService
{
    /**
     * Envia um PDF para o paperless-ngx processar o OCR em segundo plano.
     * Não bloqueia nem falha o upload caso o paperless esteja indisponível.
     *
     * @return bool true se o arquivo foi enfileirado com sucesso para OCR.
     */
    public function enviarParaOcr(Arquivo $arquivo): bool
    {
        if ($arquivo->extensao !== 'pdf') {
            return false;
        }

        $url = config('services.paperless.internal_url');
        $token = config('services.paperless.token');

        if (!$url || !$token) {
            return false;
        }

        try {
            $conteudo = Storage::disk('arquivos')->get($arquivo->caminho);

            Http::withToken($token, 'Token')
                ->timeout(15)
                ->attach('document', $conteudo, $arquivo->nome_original)
                ->post(rtrim($url, '/') . '/api/documents/post_document/', [
                    'title' => $this->tituloCorrelacao($arquivo),
                ])
                ->throw();

            $arquivo->update(['ocr_status' => 'pendente']);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar arquivo para OCR no paperless-ngx', [
                'arquivo_id' => $arquivo->id,
                'erro' => $e->getMessage(),
            ]);

            $arquivo->update(['ocr_status' => 'falhou']);

            return false;
        }
    }

    /**
     * Título usado no paperless para depois correlacionar o documento
     * processado de volta com o Arquivo correspondente na intranet.
     */
    public function tituloCorrelacao(Arquivo $arquivo): string
    {
        return "intranet-arquivo-{$arquivo->id}";
    }

    /**
     * Extrai o ID do Arquivo a partir do título gerado por tituloCorrelacao().
     */
    public function arquivoIdDoTitulo(?string $titulo): ?int
    {
        if ($titulo && preg_match('/^intranet-arquivo-(\d+)$/', $titulo, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Busca os dados de um documento processado na API do paperless-ngx.
     */
    public function buscarDocumento(int $documentId): ?array
    {
        $url = config('services.paperless.internal_url');
        $token = config('services.paperless.token');

        if (!$url || !$token) {
            return null;
        }

        $response = Http::withToken($token, 'Token')
            ->timeout(15)
            ->get(rtrim($url, '/') . "/api/documents/{$documentId}/");

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Baixa a versão arquivada (com a camada de texto do OCR já embutida,
     * permitindo seleção/cópia de texto) de um documento processado.
     * Por padrão a API do paperless retorna essa versão "archive";
     * usar ?original=true devolveria o arquivo original sem OCR.
     */
    public function baixarPdfComOcr(int $documentId): ?string
    {
        $url = config('services.paperless.internal_url');
        $token = config('services.paperless.token');

        if (!$url || !$token) {
            return null;
        }

        $response = Http::withToken($token, 'Token')
            ->timeout(30)
            ->get(rtrim($url, '/') . "/api/documents/{$documentId}/download/");

        return $response->successful() ? $response->body() : null;
    }
}
