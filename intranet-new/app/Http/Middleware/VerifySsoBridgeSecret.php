<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege os endpoints internos de ponte AD (usados pelo SPI customizado do
 * Keycloak) com um segredo compartilhado, já que essas rotas efetivamente
 * validam senha do AD e não podem ficar abertas a qualquer chamador que
 * alcance a rede interna.
 *
 * Não substitui a restrição de rede (o endpoint não deve ser exposto no
 * vhost público) — é uma segunda camada de defesa.
 */
class VerifySsoBridgeSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = config('services.sso_bridge.secret');

        if (! $configured) {
            Log::error('SSO_BRIDGE_SECRET não configurado — recusando chamada à ponte AD.');

            abort(503);
        }

        $provided = $request->bearerToken() ?? '';

        if (! hash_equals($configured, $provided)) {
            Log::warning('Tentativa de acesso à ponte AD com segredo inválido.', [
                'ip' => $request->ip(),
            ]);

            abort(401);
        }

        return $next($request);
    }
}
