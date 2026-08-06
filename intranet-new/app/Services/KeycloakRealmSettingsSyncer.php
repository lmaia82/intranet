<?php

namespace App\Services;

use App\Models\Configuracao;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Aplica no realm do Keycloak os tempos de sessão configurados em
 * Admin > Configurações (App\Http\Controllers\AdminController::atualizarSso).
 *
 * Usa um client de serviço dedicado (intranet-admin-sync), com a role
 * mínima necessária — "manage-realm" — restrita ao próprio realm
 * "intranet" (não ao master), sem acesso nenhum a usuários ou senhas.
 */
class KeycloakRealmSettingsSyncer
{
    public function sincronizar(Configuracao $config): void
    {
        $token = $this->obterToken();

        if (! $token) {
            return;
        }

        $baseUrl = rtrim(config('services.keycloak.base_url'), '/');
        $realm = config('services.keycloak.realms');

        $response = Http::withToken($token)->put("{$baseUrl}/admin/realms/{$realm}", [
            'ssoSessionIdleTimeout' => $config->sso_inatividade_minutos * 60,
            'ssoSessionMaxLifespan' => $config->sso_duracao_maxima_horas * 3600,
            // "Lembrar-me" desligado no realm inteiro: é o que faz o cookie
            // de sessão do Keycloak não sobreviver ao fechar o navegador.
            'rememberMe' => ! $config->sso_exigir_login_ao_fechar_navegador,
        ]);

        if ($response->failed()) {
            Log::warning('Falha ao sincronizar configurações de sessão com o Keycloak', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    private function obterToken(): ?string
    {
        $baseUrl = rtrim(config('services.keycloak.base_url'), '/');
        $realm = config('services.keycloak.realms');

        try {
            $response = Http::asForm()->post("{$baseUrl}/realms/{$realm}/protocol/openid-connect/token", [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.keycloak.admin_sync_client_id'),
                'client_secret' => config('services.keycloak.admin_sync_client_secret'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Não foi possível obter token para sincronizar configurações com o Keycloak', [
                'erro' => $e->getMessage(),
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Token de sincronização com o Keycloak recusado', [
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json('access_token');
    }
}
