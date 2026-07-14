<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Evento;
use App\Models\EventoGravado;
use App\Models\Informativo;
use App\Models\Pasta;
use App\Models\Telefone;
use App\Models\Tutorial;
use Illuminate\Http\Request;

class BuscaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $user = auth()->user();
        $resultados = [];

        if ($q !== '') {
            $termo = "%{$q}%";

            if ($user->hasPermission('ramais.ver')) {
                $resultados['ramais'] = Telefone::where('nome', 'like', $termo)
                    ->orWhere('telefone', 'like', $termo)
                    ->orWhere('email', 'like', $termo)
                    ->orWhere('cargo', 'like', $termo)
                    ->take(20)
                    ->get();
            }

            if ($user->hasPermission('informativos.ver')) {
                $resultados['informativos'] = Informativo::where(function ($query) use ($termo) {
                        $query->where('title', 'like', $termo)->orWhere('content', 'like', $termo);
                    })
                    ->get()
                    ->filter(fn ($informativo) => !$informativo->is_private || $user->is_admin || $informativo->sector_id === $user->sector_id)
                    ->take(20)
                    ->values();
            }

            if ($user->hasPermission('eventos.ver')) {
                $resultados['eventos'] = Evento::where('title', 'like', $termo)->take(20)->get();
                $resultados['eventos_gravados'] = EventoGravado::where('titulo', 'like', $termo)->take(20)->get();
            }

            if ($user->hasPermission('tutoriais.ver')) {
                $resultados['tutoriais'] = Tutorial::where('titulo', 'like', $termo)->take(20)->get();
            }

            if ($user->hasPermission('repositorio.ver')) {
                $resultados['pastas'] = Pasta::where('nome', 'like', $termo)
                    ->get()
                    ->filter(fn ($pasta) => $pasta->visivelPara($user))
                    ->take(20)
                    ->values();

                $resultados['arquivos'] = Arquivo::where('nome_original', 'like', $termo)
                    ->with('sector')
                    ->get()
                    ->filter(fn ($arquivo) => $arquivo->visivelPara($user))
                    ->take(20)
                    ->values();
            }
        }

        return view('busca.index', compact('q', 'resultados'));
    }
}
