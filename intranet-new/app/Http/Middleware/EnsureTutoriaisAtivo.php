<?php

namespace App\Http\Middleware;

use App\Models\Configuracao;
use Closure;
use Illuminate\Http\Request;

class EnsureTutoriaisAtivo
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(Configuracao::atual()->tutoriais_ativo, 404);

        return $next($request);
    }
}
