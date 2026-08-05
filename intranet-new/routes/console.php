<?php

use App\Services\HealthCheckService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('usuarios:desativar-expirados-ad')->daily();

// Heartbeat para o painel de Saúde do Sistema provar que o container
// "scheduler" está de fato rodando `php artisan schedule:run` a cada
// minuto — ver HealthCheckService::verificarScheduler().
//
// Guarda o timestamp (int), não o objeto Carbon: config/cache.php tem
// 'serializable_classes' => false (padrão do Laravel, contra gadget-chain
// attack se a APP_KEY vazar) — desserializar um objeto do cache vira
// __PHP_Incomplete_Class nesse modo, quebrando verificarScheduler().
Schedule::call(fn () => Cache::put(HealthCheckService::CACHE_KEY_HEARTBEAT_SCHEDULER, now()->timestamp, now()->addMinutes(10)))
    ->everyMinute();
