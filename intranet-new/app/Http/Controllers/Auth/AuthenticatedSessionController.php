<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Arquivo;
use App\Models\Destaque;
use App\Models\Evento;
use App\Models\EventoGravado;
use App\Models\Informativo;
use App\Models\Tutorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view, com uma prévia pública das funcionalidades da
     * intranet para dar visibilidade a quem ainda não entrou.
     */
    public function create(): View
    {
        $destaques = Destaque::ativos()->get();
        $informativos = Informativo::where('is_private', false)->with('sector')->latest('published_at')->take(3)->get();
        $eventos = Evento::where('dt_start', '>=', now()->toDateString())->orderBy('dt_start')->take(3)->get();
        $tutoriais = Tutorial::latest('data')->take(3)->get();
        $eventosGravados = EventoGravado::latest('data')->take(3)->get();
        $documentosPublicos = Arquivo::where('is_private', false)->with('sector')->latest('data')->take(3)->get();

        return view('auth.login', compact(
            'destaques', 'informativos', 'eventos', 'tutoriais', 'eventosGravados', 'documentosPublicos'
        ));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
