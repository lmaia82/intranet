<?php

namespace App\Http\Controllers;

use App\Models\Informativo;
use App\Models\Sector;
use Illuminate\Http\Request;

class InformativoController extends Controller
{
    public function index(Request $request)
    {
        $query = Informativo::with('sector')->latest('published_at');

        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        $informativos = $query->paginate(10)->withQueryString();
        $sectors = Sector::orderBy('name')->get();

        return view('informativos.index', compact('informativos', 'sectors'));
    }

    public function show(Informativo $informativo)
    {
        return view('informativos.show', compact('informativo'));
    }

    public function create()
    {
        $sectors = Sector::orderBy('name')->get();
        return view('informativos.create', compact('sectors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'sector_id' => 'nullable|exists:sectors,id',
            'is_private' => 'boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $validated['is_private'] = $request->boolean('is_private');
        $validated['published_at'] = now();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('informativos', 'public');
        }

        Informativo::create($validated);

        return redirect()->route('informativos.index')->with('status', 'Informativo publicado com sucesso.');
    }

    public function edit(Informativo $informativo)
    {
        $sectors = Sector::orderBy('name')->get();
        return view('informativos.edit', compact('informativo', 'sectors'));
    }

    public function update(Request $request, Informativo $informativo)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'sector_id' => 'nullable|exists:sectors,id',
            'is_private' => 'boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $validated['is_private'] = $request->boolean('is_private');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('informativos', 'public');
        }

        $informativo->update($validated);

        return redirect()->route('informativos.index')->with('status', 'Informativo atualizado com sucesso.');
    }

    public function destroy(Informativo $informativo)
    {
        $informativo->delete();
        return redirect()->route('informativos.index')->with('status', 'Informativo removido.');
    }
}
