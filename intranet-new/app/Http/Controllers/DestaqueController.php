<?php

namespace App\Http\Controllers;

use App\Models\Destaque;
use Illuminate\Http\Request;

class DestaqueController extends Controller
{
    public function index()
    {
        $destaques = Destaque::orderBy('ordem')->get();

        return view('destaques.index', compact('destaques'));
    }

    public function create()
    {
        return view('destaques.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'nullable|string|max:150',
            'imagem' => 'required|image|max:4096',
            'link' => 'nullable|url|max:255',
            'ordem' => 'nullable|integer',
            'ativo' => 'boolean',
        ]);

        $validated['ativo'] = $request->boolean('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;
        $validated['imagem'] = $request->file('imagem')->store('destaques', 'public');

        Destaque::create($validated);

        return redirect()->route('destaques.index')->with('status', 'Destaque cadastrado com sucesso.');
    }

    public function edit(Destaque $destaque)
    {
        return view('destaques.edit', compact('destaque'));
    }

    public function update(Request $request, Destaque $destaque)
    {
        $validated = $request->validate([
            'titulo' => 'nullable|string|max:150',
            'imagem' => 'nullable|image|max:4096',
            'link' => 'nullable|url|max:255',
            'ordem' => 'nullable|integer',
            'ativo' => 'boolean',
        ]);

        $validated['ativo'] = $request->boolean('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        if ($request->hasFile('imagem')) {
            $validated['imagem'] = $request->file('imagem')->store('destaques', 'public');
        }

        $destaque->update($validated);

        return redirect()->route('destaques.index')->with('status', 'Destaque atualizado com sucesso.');
    }

    public function destroy(Destaque $destaque)
    {
        $destaque->delete();
        return redirect()->route('destaques.index')->with('status', 'Destaque removido.');
    }
}
