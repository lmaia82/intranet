<?php

namespace Tests\Feature;

use App\Services\HealthCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HealthCheckServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_verificarBancoDeDados_retorna_true(): void
    {
        $resultado = app(HealthCheckService::class)->verificarBancoDeDados();

        $this->assertEquals('Banco de Dados', $resultado['nome']);
        $this->assertTrue($resultado['disponivel']);
    }

    public function test_verificarArmazenamento_retorna_true_quando_minio_responde(): void
    {
        Config::set('filesystems.disks.arquivos.endpoint', 'http://minio-teste:9000');
        Http::fake(['minio-teste:9000/*' => Http::response('', 200)]);

        $resultado = app(HealthCheckService::class)->verificarArmazenamento();

        $this->assertTrue($resultado['disponivel']);
    }

    public function test_verificarArmazenamento_retorna_false_quando_endpoint_nao_configurado(): void
    {
        Config::set('filesystems.disks.arquivos.endpoint', null);

        $resultado = app(HealthCheckService::class)->verificarArmazenamento();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarArmazenamento_retorna_false_quando_minio_falha(): void
    {
        Config::set('filesystems.disks.arquivos.endpoint', 'http://minio-teste:9000');
        Http::fake(['minio-teste:9000/*' => Http::response('erro', 500)]);

        $resultado = app(HealthCheckService::class)->verificarArmazenamento();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarEmail_retorna_false_quando_host_nao_configurado(): void
    {
        Config::set('mail.mailers.smtp.host', null);
        Config::set('mail.mailers.smtp.port', null);

        $resultado = app(HealthCheckService::class)->verificarEmail();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarEmail_retorna_false_quando_nao_consegue_conectar(): void
    {
        Config::set('mail.mailers.smtp.host', 'host-inexistente.invalido');
        Config::set('mail.mailers.smtp.port', 2525);

        $resultado = app(HealthCheckService::class)->verificarEmail();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarOnlyOffice_retorna_true_quando_responde(): void
    {
        Config::set('services.onlyoffice.internal_url', 'http://onlyoffice-teste');
        Http::fake(['onlyoffice-teste/*' => Http::response('true', 200)]);

        $resultado = app(HealthCheckService::class)->verificarOnlyOffice();

        $this->assertTrue($resultado['disponivel']);
    }

    public function test_verificarOnlyOffice_retorna_false_quando_nao_configurado(): void
    {
        Config::set('services.onlyoffice.internal_url', null);

        $resultado = app(HealthCheckService::class)->verificarOnlyOffice();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarStirlingPdf_retorna_true_quando_responde(): void
    {
        Config::set('services.stirling_pdf.internal_url', 'http://stirling-teste');
        Http::fake(['stirling-teste/*' => Http::response(['status' => 'UP'], 200)]);

        $resultado = app(HealthCheckService::class)->verificarStirlingPdf();

        $this->assertTrue($resultado['disponivel']);
    }

    public function test_verificarStirlingPdf_retorna_false_quando_falha(): void
    {
        Config::set('services.stirling_pdf.internal_url', 'http://stirling-teste');
        Http::fake(['stirling-teste/*' => Http::response('erro', 500)]);

        $resultado = app(HealthCheckService::class)->verificarStirlingPdf();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarPaperless_retorna_true_quando_disponivel(): void
    {
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Http::fake(['paperless-teste/*' => Http::response(['results' => []], 200)]);

        $resultado = app(HealthCheckService::class)->verificarPaperless();

        $this->assertEquals('Paperless-ngx (OCR)', $resultado['nome']);
        $this->assertTrue($resultado['disponivel']);
    }

    public function test_verificarScheduler_retorna_true_quando_heartbeat_recente(): void
    {
        Cache::put(HealthCheckService::CACHE_KEY_HEARTBEAT_SCHEDULER, now());

        $resultado = app(HealthCheckService::class)->verificarScheduler();

        $this->assertEquals('Agendador (scheduler)', $resultado['nome']);
        $this->assertTrue($resultado['disponivel']);
    }

    public function test_verificarScheduler_retorna_false_quando_heartbeat_antigo(): void
    {
        Cache::put(HealthCheckService::CACHE_KEY_HEARTBEAT_SCHEDULER, now()->subMinutes(5));

        $resultado = app(HealthCheckService::class)->verificarScheduler();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarScheduler_retorna_false_quando_nunca_rodou(): void
    {
        Cache::forget(HealthCheckService::CACHE_KEY_HEARTBEAT_SCHEDULER);

        $resultado = app(HealthCheckService::class)->verificarScheduler();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarPortainer_retorna_true_quando_responde(): void
    {
        Config::set('services.portainer.internal_url', 'https://portainer-teste:9443');
        Http::fake(['portainer-teste:9443/*' => Http::response(['Version' => '2.33.6'], 200)]);

        $resultado = app(HealthCheckService::class)->verificarPortainer();

        $this->assertEquals('Portainer', $resultado['nome']);
        $this->assertTrue($resultado['disponivel']);
    }

    public function test_verificarPortainer_retorna_false_quando_nao_configurado(): void
    {
        Config::set('services.portainer.internal_url', null);

        $resultado = app(HealthCheckService::class)->verificarPortainer();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarPortainer_retorna_false_quando_falha(): void
    {
        Config::set('services.portainer.internal_url', 'https://portainer-teste:9443');
        Http::fake(['portainer-teste:9443/*' => Http::response('erro', 500)]);

        $resultado = app(HealthCheckService::class)->verificarPortainer();

        $this->assertFalse($resultado['disponivel']);
    }

    public function test_verificarTodos_retorna_oito_servicos(): void
    {
        Config::set('filesystems.disks.arquivos.endpoint', 'http://minio-teste:9000');
        Config::set('services.onlyoffice.internal_url', 'http://onlyoffice-teste');
        Config::set('services.stirling_pdf.internal_url', 'http://stirling-teste');
        Config::set('services.paperless.internal_url', 'http://paperless-teste');
        Config::set('services.paperless.token', 'token-teste');
        Config::set('services.portainer.internal_url', 'https://portainer-teste:9443');
        Http::fake(['*' => Http::response(['status' => 'UP', 'results' => []], 200)]);

        $servicos = app(HealthCheckService::class)->verificarTodos();

        $this->assertCount(8, $servicos);
    }
}
