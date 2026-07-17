<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Destaque;
use App\Models\Evento;
use App\Models\EventoGravado;
use App\Models\Informativo;
use App\Models\Tutorial;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const MESES_PT_BR = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();

        $destaques = Destaque::ativos()->get();

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
            ? Arquivo::where('is_private', false)
                ->whereDoesntHave('pasta', fn ($query) => $query->whereNotNull('user_id'))
                ->with('sector')
                ->latest('data')
                ->take(5)
                ->get()
            : collect();

        $mes = (int) $request->query('mes', now()->month);
        $ano = (int) $request->query('ano', now()->year);
        if ($mes < 1 || $mes > 12) {
            $mes = now()->month;
        }

        $mesReferencia = Carbon::create($ano, $mes, 1)->startOfDay();
        $nomeMesAno = self::MESES_PT_BR[$mesReferencia->month] . ' de ' . $mesReferencia->year;
        $mesAnterior = $mesReferencia->copy()->subMonthNoOverflow();
        $mesProximo = $mesReferencia->copy()->addMonthNoOverflow();

        $inicioGrade = $mesReferencia->copy()->startOfWeek(Carbon::SUNDAY);
        $fimGrade = $mesReferencia->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $hoje = now()->toDateString();
        $diasCalendario = collect();
        for ($dia = $inicioGrade->copy(); $dia->lte($fimGrade); $dia->addDay()) {
            $diasCalendario->push([
                'data' => $dia->copy(),
                'foraDoMes' => $dia->month !== $mesReferencia->month,
                'hoje' => $dia->toDateString() === $hoje,
            ]);
        }

        $eventosPorDia = Evento::whereBetween('dt_start', [$inicioGrade->toDateString(), $fimGrade->toDateString()])
            ->orderBy('dt_start')
            ->get()
            ->groupBy(fn ($evento) => $evento->dt_start->toDateString());

        return view('dashboard', compact(
            'destaques', 'informativos', 'eventos', 'meusArquivos',
            'tutoriais', 'eventosGravados', 'documentosPublicos',
            'mesReferencia', 'nomeMesAno', 'mesAnterior', 'mesProximo',
            'diasCalendario', 'eventosPorDia'
        ));
    }
}
