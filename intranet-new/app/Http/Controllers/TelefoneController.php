<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\Telefone;
use Illuminate\Http\Request;

class TelefoneController extends Controller
{
    public function index(Request $request)
    {
        $query = Telefone::with('sector')->orderBy('nome');

        if ($request->filled('letra')) {
            $query->where('nome', 'like', $request->letra . '%');
        }
        if ($request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->busca . '%');
        }

        $telefones = $query->paginate(20)->withQueryString();
        $letras = Telefone::selectRaw('DISTINCT UPPER(LEFT(nome,1)) as letra')->orderBy('letra')->pluck('letra');

        return view('telefones.index', compact('telefones', 'letras'));
    }

    public function create()
    {
        $sectors = Sector::orderBy('name')->get();
        return view('telefones.create', compact('sectors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'telefone' => 'required|string|max:100',
            'sector_id' => 'required|exists:sectors,id',
            'email' => 'nullable|email|max:100',
            'cargo' => 'nullable|string|max:100',
        ]);

        Telefone::create($validated);

        return redirect()->route('telefones.index')->with('status', 'Ramal cadastrado com sucesso.');
    }

    public function edit(Telefone $telefone)
    {
        $sectors = Sector::orderBy('name')->get();
        return view('telefones.edit', compact('telefone', 'sectors'));
    }

    public function update(Request $request, Telefone $telefone)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'telefone' => 'required|string|max:100',
            'sector_id' => 'required|exists:sectors,id',
            'email' => 'nullable|email|max:100',
            'cargo' => 'nullable|string|max:100',
        ]);

        $telefone->update($validated);

        return redirect()->route('telefones.index')->with('status', 'Ramal atualizado com sucesso.');
    }

    public function destroy(Telefone $telefone)
    {
        $telefone->delete();
        return redirect()->route('telefones.index')->with('status', 'Ramal removido.');
    }
}
