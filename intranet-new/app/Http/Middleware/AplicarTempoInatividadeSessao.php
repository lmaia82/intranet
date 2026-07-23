<?php

namespace App\Http\Middleware;

use App\Models\Configuracao;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Aplica o tempo de inatividade configurado em Admin > Configurações ao
 * tempo de vida da sessão, antes do StartSession processar a requisição —
 * é o mecanismo padrão do Laravel (expira N minutos após a última
 * atividade) que já faz o que precisamos, só que configurável pelo admin
 * em vez de fixo no .env.
 */
class AplicarTempoInatividadeSessao
{
    public function handle(Request $request, Closure $next)
    {
        // A tabela pode não existir ainda (instalação nova antes das
        // migrations, ou testes que não usam RefreshDatabase) — nesse caso
        // mantém o valor padrão do config/session.php.
        if (Schema::hasTable('configuracoes')) {
            config(['session.lifetime' => Configuracao::atual()->tempo_inatividade_minutos]);
        }

        return $next($request);
    }
}
