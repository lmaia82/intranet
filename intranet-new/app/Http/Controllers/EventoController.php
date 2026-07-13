<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoGravado;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        $proximos = Evento::where('dt_start', '>=', now()->toDateString())->orderBy('dt_start')->get();
        $anteriores = Evento::where('dt_start', '<', now()->toDateString())->orderBy('dt_start', 'desc')->paginate(10);
        $gravados = EventoGravado::orderBy('data', 'desc')->get();

        return view('eventos.index', compact('proximos', 'anteriores', 'gravados'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'local' => 'required|string|max:100',
            'informacoes' => 'nullable|string',
            'dt_start' => 'required|date',
            'dt_end' => 'nullable|date|after_or_equal:dt_start',
            'tm_start' => 'nullable',
            'tm_end' => 'nullable',
        ]);

        Evento::create($validated);

        return redirect()->route('eventos.index')->with('status', 'Evento cadastrado com sucesso.');
    }

    public function edit(Evento $evento)
    {
        return view('eventos.edit', compact('evento'));
    }

    public function update(Request $request, Evento $evento)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'local' => 'required|string|max:100',
            'informacoes' => 'nullable|string',
            'dt_start' => 'required|date',
            'dt_end' => 'nullable|date|after_or_equal:dt_start',
            'tm_start' => 'nullable',
            'tm_end' => 'nullable',
        ]);

        $evento->update($validated);

        return redirect()->route('eventos.index')->with('status', 'Evento atualizado com sucesso.');
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();
        return redirect()->route('eventos.index')->with('status', 'Evento removido.');
    }
}
