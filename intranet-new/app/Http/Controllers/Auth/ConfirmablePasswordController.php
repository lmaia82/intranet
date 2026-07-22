<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActiveDirectoryAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        // Mesma verificação do login (App\Http\Requests\Auth\LoginRequest):
        // tenta o bind direto no AD e, se não conferir, cai no fallback
        // local (usuários administrados só na intranet).
        $confirmadoNoAd = app(ActiveDirectoryAuthenticator::class)
            ->autenticar($request->user()->email, $request->password);

        $confirmado = $confirmadoNoAd
            || Auth::guard('web')->validate([
                'email' => $request->user()->email,
                'password' => $request->password,
            ]);

        if (! $confirmado) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
