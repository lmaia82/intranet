<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito a administradores.');

        return $next($request);
    }
}
