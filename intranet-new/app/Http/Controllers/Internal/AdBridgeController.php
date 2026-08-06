<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\ActiveDirectoryAuthenticator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ponte usada pelo SPI customizado do Keycloak (docker/sso/keycloak/ad-bridge-spi/)
 * para validar login/senha no AD sem que o Keycloak precise de uma conta de
 * serviço própria. Reusa ActiveDirectoryAuthenticator — a mesma lógica de
 * bind direto já usada pela importação em lote — como única fonte de
 * verdade, em vez de duplicar a integração com o AD em Java.
 *
 * Protegido por VerifySsoBridgeSecret (segredo compartilhado) — ver
 * routes/internal.php. Também deve ficar restrito, na infra, a só aceitar
 * chamadas vindas do container do Keycloak (não expor no vhost público).
 */
class AdBridgeController extends Controller
{
    public function __construct(private ActiveDirectoryAuthenticator $authenticator)
    {
    }

    public function authenticate(Request $request): JsonResponse
    {
        // Sem validação de formato de e-mail: o Keycloak encaminha o que o
        // usuário digitou, que agora é só o login sem "@dominio" (ex:
        // "lgoncalves") — um e-mail completo também chega aqui sem problema.
        $data = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $usuario = $this->authenticator->autenticar($data['email'], $data['password']);

        if (! $usuario) {
            // Cobre tanto "senha incorreta/usuário inexistente no AD" quanto
            // "conta desativada na intranet" — o SPI não precisa distinguir
            // os dois casos, ambos significam "sem acesso ao SSO".
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'id' => $usuario->getKey(),
            'email' => $usuario->email,
            'name' => $usuario->name,
            'is_admin' => (bool) $usuario->is_admin,
        ]);
    }
}
