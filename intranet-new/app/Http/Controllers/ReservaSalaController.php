<?php

namespace App\Http\Controllers;

use App\Models\ReservaSala;
use App\Models\Sala;
use App\Models\Sector;
use App\Services\ReservaSalaConflictChecker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservaSalaController extends Controller
{
    private const MESES_PT_BR = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    public function index(Request $request)
    {
        $salas = Sala::orderBy('ordem')->get();

        $termo = trim((string) $request->query('q', ''));

        if ($termo !== '') {
            $reservasEncontradas = ReservaSala::with(['sala', 'user', 'sector'])
                ->where(function ($query) use ($termo) {
                    $query->where('titulo', 'like', "%{$termo}%")
                        ->orWhere('solicitante', 'like', "%{$termo}%")
                        ->orWhere('tipo_evento', 'like', "%{$termo}%")
                        ->orWhereHas('sala', fn ($q) => $q->where('nome', 'like', "%{$termo}%"))
                        ->orWhereHas('sector', fn ($q) => $q->where('nome', 'like', "%{$termo}%"));
                })
                ->orderBy('data')->orderBy('horario_inicio')
                ->get();

            return view('reservas-sala.index', [
                'salas' => $salas,
                'termo' => $termo,
                'reservasEncontradas' => $reservasEncontradas,
            ] + $this->dadosCalendario($request, $salas));
        }

        return view('reservas-sala.index', [
            'salas' => $salas,
            'termo' => '',
            'reservasEncontradas' => null,
        ] + $this->dadosCalendario($request, $salas));
    }

    private function dadosCalendario(Request $request, $salas): array
    {
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

        $reservasPorDia = ReservaSala::with(['sala', 'user'])
            ->whereBetween('data', [$inicioGrade->toDateString(), $fimGrade->toDateString()])
            ->orderBy('horario_inicio')
            ->get()
            ->groupBy(fn ($reserva) => $reserva->data->toDateString());

        return compact('mesReferencia', 'nomeMesAno', 'mesAnterior', 'mesProximo', 'diasCalendario', 'reservasPorDia');
    }

    public function create()
    {
        $salas = Sala::orderBy('ordem')->get();
        $sectors = Sector::orderBy('nome')->get();

        return view('reservas-sala.create', compact('salas', 'sectors'));
    }

    public function store(Request $request)
    {
        $sala = Sala::findOrFail($request->input('sala_id'));

        $validated = $this->validarDados($request, $sala);

        if (ReservaSalaConflictChecker::existeConflito($sala, $validated['data'], $validated['horario_inicio'], $validated['horario_fim'])) {
            return back()->withErrors([
                'horario_inicio' => 'Já existe uma reserva muito próxima a esse horário para esta sala. É preciso um intervalo de pelo menos 1 hora.',
            ])->withInput();
        }

        $validated['user_id'] = auth()->id();
        $validated['sector_id'] = $validated['sector_id'] ?? auth()->user()->sector_id;
        $validated['solicitante'] = ($validated['solicitante'] ?? null) ?: auth()->user()->name;

        ReservaSala::create($validated);

        return redirect()->route('reservas-sala.index')->with('status', 'Reserva confirmada com sucesso.');
    }

    public function show(ReservaSala $reservaSala)
    {
        $reservaSala->load(['sala', 'user', 'sector']);

        return view('reservas-sala.show', ['reserva' => $reservaSala]);
    }

    public function edit(ReservaSala $reservaSala)
    {
        abort_unless($reservaSala->pertenceA(auth()->user()), 403, 'Você só pode editar as suas próprias reservas.');

        $salas = Sala::orderBy('ordem')->get();
        $sectors = Sector::orderBy('nome')->get();

        return view('reservas-sala.edit', ['reserva' => $reservaSala] + compact('salas', 'sectors'));
    }

    public function update(Request $request, ReservaSala $reservaSala)
    {
        abort_unless($reservaSala->pertenceA(auth()->user()), 403, 'Você só pode editar as suas próprias reservas.');

        $sala = Sala::findOrFail($request->input('sala_id'));

        $validated = $this->validarDados($request, $sala, $reservaSala);

        if (ReservaSalaConflictChecker::existeConflito($sala, $validated['data'], $validated['horario_inicio'], $validated['horario_fim'], $reservaSala->id)) {
            return back()->withErrors([
                'horario_inicio' => 'Esta alteração causa conflito com outra reserva. É preciso um intervalo de pelo menos 1 hora.',
            ])->withInput();
        }

        $validated['solicitante'] = ($validated['solicitante'] ?? null) ?: $reservaSala->solicitante;

        $reservaSala->update($validated);

        return redirect()->route('reservas-sala.index')->with('status', 'Reserva atualizada com sucesso.');
    }

    public function destroy(ReservaSala $reservaSala)
    {
        abort_unless($reservaSala->pertenceA(auth()->user()), 403, 'Você só pode excluir as suas próprias reservas.');

        $reservaSala->delete();

        return redirect()->route('reservas-sala.index')->with('status', 'Reserva excluída com sucesso.');
    }

    private function validarDados(Request $request, Sala $sala, ?ReservaSala $reservaAtual = null): array
    {
        $validated = $request->validate([
            'sala_id' => 'required|exists:salas,id',
            'titulo' => 'required|string|max:150',
            'data' => 'required|date|after_or_equal:' . now()->toDateString(),
            'data_termino' => 'nullable|date|after_or_equal:data',
            'horario_inicio' => 'required|date_format:H:i',
            'horario_fim' => 'required|date_format:H:i|after:horario_inicio',
            'solicitante' => 'nullable|string|max:100',
            'sector_id' => 'nullable|exists:sectors,id',
            'tipo_evento' => 'required|string|max:100',
            'publico' => 'required|in:presencial,virtual,hibrido',
            'visita_externa' => 'nullable|boolean',
            'observacoes' => 'nullable|string',

            'equipamentos' => 'required|array|min:1',
            'equipamentos.*' => 'string',
            'salavirtual' => ['nullable', 'string', Rule::requiredIf(function () use ($request) {
                $equipamentos = $request->input('equipamentos', []);
                return in_array('Virtual', $equipamentos) || in_array('Videoconferência', $equipamentos);
            })],

            'wifi_tipo' => 'nullable|in:evento,individual',
            'wifi_nome_evento' => ['nullable', 'string', 'max:150', Rule::requiredIf(function () use ($request) {
                return in_array('WiFi Visitante', $request->input('equipamentos', [])) && $request->input('wifi_tipo') === 'evento';
            })],
            'wifi_visitantes' => 'nullable|array',
            'wifi_visitantes.*.nome' => 'nullable|string|max:150',
            'wifi_visitantes.*.email' => 'nullable|email|max:150',

            'servicos' => 'required|array|min:1',
            'servicos.*' => 'string',
            'arrumacao' => ['nullable', 'string', Rule::requiredIf(fn () => $sala->permite_arrumacao)],

            'comunicacao' => 'required|array|min:1',
            'comunicacao.*' => 'string',
            'divulgacao' => ['nullable', 'string', Rule::requiredIf(function () use ($request) {
                return in_array('Divulgação', $request->input('comunicacao', []));
            })],

            'permissao_especial' => 'nullable|boolean',
        ], [
            'arrumacao.required' => 'Selecione a arrumação da sala.',
            'salavirtual.required' => 'Selecione a plataforma da sala virtual.',
            'wifi_nome_evento.required' => 'Informe o nome do evento/grupo para a senha de WiFi.',
            'divulgacao.required' => 'Selecione onde a divulgação será feita.',
            'equipamentos.required' => 'Selecione ao menos uma opção de equipamentos (ou "Não será necessário").',
            'servicos.required' => 'Selecione ao menos uma opção de serviços (ou "Não será necessário").',
            'comunicacao.required' => 'Selecione ao menos uma opção de comunicação (ou "Não será necessário").',
        ]);

        if ($sala->restrita && !$request->boolean('permissao_especial')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'permissao_especial' => 'Esta sala é restrita: é preciso confirmar a autorização prévia da chefia/coordenação.',
            ]);
        }

        // Campos condicionais (só existem em $validated quando enviados) —
        // normaliza para sempre estarem presentes, mesmo como null, para
        // que uma edição realmente limpe um valor antigo que não se aplica
        // mais (ex: desmarcar "Divulgação" precisa zerar 'divulgacao').
        $validated += [
            'solicitante' => null,
            'sector_id' => null,
            'observacoes' => null,
            'salavirtual' => null,
            'wifi_tipo' => null,
            'wifi_nome_evento' => null,
            'wifi_visitantes' => null,
            'arrumacao' => null,
            'divulgacao' => null,
            'data_termino' => null,
        ];

        if (!$sala->permite_arrumacao) {
            $validated['arrumacao'] = null;
        }

        if (empty($request->input('wifi_visitantes'))) {
            $validated['wifi_visitantes'] = null;
        } else {
            $validated['wifi_visitantes'] = array_values(array_filter(
                $request->input('wifi_visitantes'),
                fn ($v) => !empty($v['nome']) || !empty($v['email'])
            ));
        }

        $validated['visita_externa'] = $request->boolean('visita_externa');
        $validated['permissao_especial'] = $request->boolean('permissao_especial');
        $validated['data_termino'] = $validated['data_termino'] ?? $validated['data'];

        return $validated;
    }

    public function relatorios(Request $request)
    {
        $mes = (int) $request->query('mes', now()->month);
        $ano = (int) $request->query('ano', now()->year);

        $salas = Sala::orderBy('ordem')->get()->map(function ($sala) use ($mes, $ano) {
            $sala->total_reservas = ReservaSala::where('sala_id', $sala->id)
                ->whereMonth('data', $mes)
                ->whereYear('data', $ano)
                ->count();
            return $sala;
        })->sortByDesc('total_reservas')->values();

        $totalEventos = $salas->sum('total_reservas');
        $salaCampea = $totalEventos > 0 ? $salas->first()->nome : null;
        $maiorTotal = $salas->max('total_reservas') ?: 1;

        return view('reservas-sala.relatorios', [
            'salas' => $salas,
            'totalEventos' => $totalEventos,
            'salaCampea' => $salaCampea,
            'maiorTotal' => $maiorTotal,
            'mes' => $mes,
            'ano' => $ano,
            'mesesNomes' => self::MESES_PT_BR,
        ]);
    }

    public function imprimirSemana()
    {
        $hoje = Carbon::today();
        $dias = collect();

        for ($i = 0; $i < 7; $i++) {
            $dia = $hoje->copy()->addDays($i);
            $dias->push([
                'data' => $dia,
                'reservas' => ReservaSala::with('sala')
                    ->where('data', $dia->toDateString())
                    ->orderBy('horario_inicio')
                    ->get(),
            ]);
        }

        return view('reservas-sala.imprimir-semana', compact('dias'));
    }
}
