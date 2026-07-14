<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Evento;
use App\Models\Informativo;

class DashboardController extends Controller
{
    public function index()
    {
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

        return view('dashboard', compact('informativos', 'eventos', 'meusArquivos'));
    }
}
