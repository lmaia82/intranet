<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HealthCheckService
{
    /**
     * Chave de cache usada pelo heartbeat agendado (ver routes/console.php)
     * para provar que o container "scheduler" está de fato chamando
     * `php artisan schedule:run` a cada minuto, e não só que o container
     * está de pé.
     */
    public const CACHE_KEY_HEARTBEAT_SCHEDULER = 'health_check_scheduler_heartbeat';

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
            $this->verificarScheduler(),
            $this->verificarPortainer(),
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

    /**
     * O comando `usuarios:desativar-expirados-ad` roda só uma vez por dia —
     * verificar diretamente esse job não provaria que o loop do container
     * "scheduler" está vivo entre uma execução e outra. Por isso um
     * heartbeat próprio roda a cada minuto (ver routes/console.php) e é
     * considerado saudável enquanto tiver menos de 2 minutos.
     */
    public function verificarScheduler(): array
    {
        $ultimoHeartbeat = Cache::get(self::CACHE_KEY_HEARTBEAT_SCHEDULER);

        if (!$ultimoHeartbeat) {
            return $this->resultado('Agendador (scheduler)', false, 'Nenhum heartbeat registrado ainda.');
        }

        $ok = $ultimoHeartbeat->diffInMinutes(now()) < 2;

        return $this->resultado('Agendador (scheduler)', $ok, $ok ? null : "Último heartbeat: {$ultimoHeartbeat->diffForHumans()}.");
    }

    public function verificarPortainer(): array
    {
        $url = config('services.portainer.internal_url');

        if (!$url) {
            return $this->resultado('Portainer', false, 'URL não configurada.');
        }

        try {
            $ok = Http::withoutVerifying()->timeout(5)->get(rtrim($url, '/') . '/api/system/status')->successful();

            return $this->resultado('Portainer', $ok);
        } catch (\Throwable $e) {
            return $this->resultado('Portainer', false, $e->getMessage());
        }
    }

    private function resultado(string $nome, bool $disponivel, ?string $detalhe = null): array
    {
        return ['nome' => $nome, 'disponivel' => $disponivel, 'detalhe' => $detalhe];
    }
}
