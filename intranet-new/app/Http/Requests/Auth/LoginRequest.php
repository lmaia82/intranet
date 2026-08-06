<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\ActiveDirectoryAuthenticator;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Não valida formato de e-mail: o login é só o usuário, sem
            // "@dominio" (ex: "lgoncalves"), igual ao padrão de rede do
            // CETEM — um e-mail completo também é aceito.
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->string('email')->value();
        $password = $this->string('password')->value();

        // Tenta autenticar via bind direto no AD (sem conta de serviço: o
        // CETEM optou por autenticar cada usuário com a própria credencial).
        // Se a senha não conferir no AD — inclusive para quem não tem conta
        // lá — cai no fallback local (usuários administrados só na intranet).
        $usuario = app(ActiveDirectoryAuthenticator::class)->autenticar($login, $password);

        if ($usuario) {
            Auth::login($usuario, $this->boolean('remember'));
        } elseif (! Auth::attempt(['email' => $this->resolverEmailLocal($login), 'password' => $password, 'is_active' => true], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Contas administradas só na intranet (sem conta no AD) são guardadas
     * com o e-mail completo em `users.email` — se o login veio sem
     * "@dominio", acha o e-mail completo pela parte antes do "@", pra não
     * quebrar o fallback local. Se não achar (ou já veio com "@"), devolve
     * o login como veio — Auth::attempt() só falha normalmente.
     */
    private function resolverEmailLocal(string $login): string
    {
        if (str_contains($login, '@')) {
            return $login;
        }

        // LIKE em vez de SUBSTRING_INDEX (específico do MySQL) — os testes
        // rodam em sqlite, e LIKE funciona igual nos dois bancos.
        return User::query()
            ->where('email', 'like', $login.'@%')
            ->value('email') ?? $login;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
