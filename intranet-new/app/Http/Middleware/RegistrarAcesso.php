<?php

namespace App\Http\Middleware;

use App\Models\Acesso;
use Closure;
use Illuminate\Http\Request;

class RegistrarAcesso
{
    public function handle(Request $request, Closure $next, string $modulo)
    {
        $response = $next($request);

        if ($request->user() && $response->getStatusCode() < 400) {
            Acesso::create(['user_id' => $request->user()->id, 'modulo' => $modulo]);
        }

        return $response;
    }
}
