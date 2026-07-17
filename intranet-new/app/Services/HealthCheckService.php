<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HealthCheckService
{
    public function __construct(private PaperlessService $paperless)
    {
    }

    /**
     * Verifica a disponibilidade de todos os serviços externos que a
     * intranet depende, para o painel de Saúde do Sistema.
     *
     * @return array<int, array{nome: string, disponivel: bool, detalhe: ?string}>
     */
    public function verificarTodos(): array
    {
        return [
            $this->verificarBancoDeDados(),
            $this->verificarArmazenamento(),
            $this->verificarEmail(),
            $this->verificarOnlyOffice(),
            $this->verificarStirlingPdf(),
            $this->verificarPaperless(),
        ];
    }

    public function verificarBancoDeDados(): array
    {
        try {
            DB::select('select 1');

            return $this->resultado('Banco de Dados', true);
        } catch (\Throwable $e) {
            return $this->resultado('Banco de Dados', false, $e->getMessage());
        }
    }

    public function verificarArmazenamento(): array
    {
        $endpoint = config('filesystems.disks.arquivos.endpoint');

        if (!$endpoint) {
            return $this->resultado('Armazenamento (MinIO)', false, 'Endpoint não configurado.');
        }

        try {
            $ok = Http::timeout(5)->get(rtrim($endpoint, '/') . '/minio/health/live')->successful();

            return $this->resultado('Armazenamento (MinIO)', $ok);
        } catch (\Throwable $e) {
            return $this->resultado('Armazenamento (MinIO)', false, $e->getMessage());
        }
    }

    public function verificarEmail(): array
    {
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');

        if (!$host || !$port) {
            return $this->resultado('E-mail (SMTP)', false, 'Não configurado.');
        }

        $conexao = @fsockopen($host, (int) $port, $codigoErro, $mensagemErro, 3);

        if ($conexao) {
            fclose($conexao);

            return $this->resultado('E-mail (SMTP)', true);
        }

        return $this->resultado('E-mail (SMTP)', false, $mensagemErro ?: 'Não foi possível conectar.');
    }

    public function verificarOnlyOffice(): array
    {
        $url = config('services.onlyoffice.internal_url');

        if (!$url) {
            return $this->resultado('OnlyOffice', false, 'URL não configurada.');
        }

        try {
            $ok = Http::timeout(5)->get(rtrim($url, '/') . '/healthcheck')->successful();

            return $this->resultado('OnlyOffice', $ok);
        } catch (\Throwable $e) {
            return $this->resultado('OnlyOffice', false, $e->getMessage());
        }
    }

    public function verificarStirlingPdf(): array
    {
        $url = config('services.stirling_pdf.internal_url');

        if (!$url) {
            return $this->resultado('Stirling PDF', false, 'URL não configurada.');
        }

        try {
            $ok = Http::timeout(5)->get(rtrim($url, '/') . '/api/v1/info/status')->successful();

            return $this->resultado('Stirling PDF', $ok);
        } catch (\Throwable $e) {
            return $this->resultado('Stirling PDF', false, $e->getMessage());
        }
    }

    public function verificarPaperless(): array
    {
        return $this->resultado('Paperless-ngx (OCR)', $this->paperless->estaDisponivel());
    }

    private function resultado(string $nome, bool $disponivel, ?string $detalhe = null): array
    {
        return ['nome' => $nome, 'disponivel' => $disponivel, 'detalhe' => $detalhe];
    }
}
