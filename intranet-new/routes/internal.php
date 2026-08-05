<?php

use App\Http\Controllers\Internal\AdBridgeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas internas
|--------------------------------------------------------------------------
|
| Não são para uso de usuários finais nem de outros sistemas da intranet —
| hoje só existe a ponte de autenticação AD consumida pelo SPI customizado
| do Keycloak (ver docker/sso/keycloak/ad-bridge-spi/). Protegidas por
| 'sso-bridge-secret' (App\Http\Middleware\VerifySsoBridgeSecret) e por
| throttle; a infra deve garantir que esse caminho não fique acessível a
| partir do vhost público, só da rede interna onde o Keycloak roda.
|
*/

Route::middleware(['sso-bridge-secret', 'throttle:30,1'])
    ->prefix('internal')
    ->group(function () {
        Route::post('ad-auth', [AdBridgeController::class, 'authenticate'])
            ->name('internal.ad-auth');
    });
