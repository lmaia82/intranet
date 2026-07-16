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

            $resposta = Http::withToken($token, 'Token')
                ->timeout(15)
                ->attach('document', $conteudo, $arquivo->nome_original)
                ->post(rtrim($url, '/') . '/api/documents/post_document/', [
                    'title' => $this->tituloCorrelacao($arquivo),
                ])
                ->throw();

            $arquivo->update([
                'ocr_status' => 'pendente',
                'paperless_task_id' => is_string($resposta->json()) ? $resposta->json() : null,
                'ocr_erro' => null,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar arquivo para OCR no paperless-ngx', [
                'arquivo_id' => $arquivo->id,
                'erro' => $e->getMessage(),
            ]);

            $arquivo->update(['ocr_status' => 'falhou', 'ocr_erro' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Verifica arquivos com OCR "pendente" contra a API do paperless-ngx e
     * atualiza o status. Cobre dois cenários que o webhook sozinho não
     * resolve: (1) o paperless rejeitou/falhou o documento (duplicata,
     * arquivo corrompido etc. — o post-consume-script só roda em sucesso,
     * então a intranet nunca ficaria sabendo) e (2) o webhook de sucesso
     * não chegou por algum motivo de rede, mas o documento já existe.
     */
    public function sincronizarPendente(Arquivo $arquivo): void
    {
        if ($arquivo->ocr_status !== 'pendente' || !$arquivo->paperless_task_id) {
            return;
        }

        $tarefa = $this->buscarTarefa($arquivo->paperless_task_id);

        if (!$tarefa) {
            return;
        }

        if ($tarefa['status'] === 'FAILURE') {
            $arquivo->update([
                'ocr_status' => 'falhou',
                'ocr_erro' => $tarefa['result'] ?? 'Falha não especificada no paperless-ngx.',
            ]);

            return;
        }

        if ($tarefa['status'] === 'SUCCESS' && !empty($tarefa['related_document'])) {
            $this->aplicarResultadoOcr($arquivo, (int) $tarefa['related_document']);
        }
    }

    /**
     * Aplica o resultado de um documento processado com sucesso a um Arquivo:
     * salva o texto extraído e substitui o arquivo original pela versão com
     * a camada de texto do OCR embutida (permitindo seleção/cópia de texto).
     */
    public function aplicarResultadoOcr(Arquivo $arquivo, int $documentId): void
    {
        $documento = $this->buscarDocumento($documentId);

        if (!$documento) {
            return;
        }

        $arquivo->update([
            'paperless_document_id' => $documentId,
            'conteudo_ocr' => $documento['content'] ?? null,
            'ocr_status' => 'concluido',
            'ocr_erro' => null,
        ]);

        try {
            $pdfComOcr = $this->baixarPdfComOcr($documentId);

            if ($pdfComOcr) {
                Storage::disk('arquivos')->put($arquivo->caminho, $pdfComOcr);
                $arquivo->update(['tamanho' => strlen($pdfComOcr)]);
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao substituir arquivo pela versão com OCR do paperless-ngx', [
                'arquivo_id' => $arquivo->id,
                'erro' => $e->getMessage(),
            ]);
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
     * Consulta o status de uma tarefa de consumo (upload) na API do paperless.
     */
    public function buscarTarefa(string $taskId): ?array
    {
        $url = config('services.paperless.internal_url');
        $token = config('services.paperless.token');

        if (!$url || !$token) {
            return null;
        }

        $response = Http::withToken($token, 'Token')
            ->timeout(15)
            ->get(rtrim($url, '/') . '/api/tasks/', ['task_id' => $taskId]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json()[0] ?? null;
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
