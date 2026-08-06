<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Login via SSO (Keycloak). O Keycloak já validou a senha no AD de verdade
 * (via o SPI ad-bridge → App\Http\Controllers\Internal\AdBridgeController)
 * — aqui só resta mapear o usuário do Keycloak pro usuário local e abrir a
 * sessão, no mesmo espírito de
 * App\Services\ActiveDirectoryAuthenticator::provisionarPrimeiroLogin (sem
 * conta de serviço, mínimo privilégio por padrão).
 */
class SsoController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('keycloak')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $keycloakUser = Socialite::driver('keycloak')->user();
        } catch (\Throwable $e) {
            Log::warning('Falha no callback do SSO (Keycloak)', ['erro' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => 'Não foi possível completar o login via SSO. Tente novamente.',
            ]);
        }

        $usuario = $this->encontrarOuCriarUsuario($keycloakUser);

        if (! $usuario->is_active) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sua conta está desativada na intranet. Fale com um administrador.',
            ]);
        }

        // "Lembrar-se" mantém a sessão viva mesmo fechando o navegador —
        // contraria a config de Admin > Configurações > SSO quando ligada
        // (ver também AplicarTempoInatividadeSessao, que cuida do cookie de
        // sessão em si).
        Auth::login($usuario, remember: ! Configuracao::atual()->sso_exigir_login_ao_fechar_navegador);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function encontrarOuCriarUsuario(SocialiteUser $keycloakUser): User
    {
        $email = $keycloakUser->getEmail();

        $usuario = User::query()->where('email', $email)->first();

        if ($usuario) {
            // Nome pode ter mudado no AD desde o último login — mantém
            // sincronizado, igual ao que o ActiveDirectoryAuthenticator já
            // faz no bind direto.
            $usuario->forceFill(['name' => $keycloakUser->getName() ?? $usuario->name])->save();

            return $usuario;
        }

        $usuario = new User([
            'name' => $keycloakUser->getName() ?? $email,
            'email' => $email,
            // Senha local nunca é usada por quem loga via SSO — só existe
            // porque a coluna é obrigatória; gerada aleatória, descartável.
            'password' => Str::random(40),
        ]);

        $usuario->group_id = Group::where('name', 'Leitores')->value('id');
        $usuario->is_active = true;
        $usuario->save();

        return $usuario;
    }
}
