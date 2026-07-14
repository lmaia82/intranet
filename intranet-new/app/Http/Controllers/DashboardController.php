<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Evento;
use App\Models\EventoGravado;
use App\Models\Informativo;
use App\Models\Tutorial;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $informativos = Informativo::with('sector')->latest('published_at')->take(5)->get();

        $eventos = Evento::where('dt_start', '>=', now()->toDateString())
            ->orderBy('dt_start')
            ->take(5)
            ->get();

        $meusArquivos = Arquivo::whereHas('pasta', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest('updated_at')
            ->take(5)
            ->get();

        $tutoriais = $user->hasPermission('tutoriais.ver')
            ? Tutorial::latest('data')->take(5)->get()
            : collect();

        $eventosGravados = $user->hasPermission('eventos.ver')
            ? EventoGravado::latest('data')->take(5)->get()
            : collect();

        $documentosPublicos = $user->hasPermission('repositorio.ver')
            ? Arquivo::where('is_private', false)->with('sector')->latest('created_at')->take(5)->get()
            : collect();

        return view('dashboard', compact(
            'informativos', 'eventos', 'meusArquivos',
            'tutoriais', 'eventosGravados', 'documentosPublicos'
        ));
    }
}
