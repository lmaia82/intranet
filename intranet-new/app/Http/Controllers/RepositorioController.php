<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RepositorioController extends Controller
{
    public function meusArquivos()
    {
        $pasta = Pasta::firstOrCreate(
            ['user_id' => auth()->id(), 'parent_id' => null],
            ['nome' => 'Meus Arquivos']
        );

        return redirect()->route('repositorio.index', ['pasta' => $pasta->id]);
    }

    public function index(?Pasta $pasta = null)
    {
        $pastaAtual = $pasta;
        $subpastas = $pastaAtual ? $pastaAtual->children : Pasta::whereNull('parent_id')->orderBy('nome')->get();
        $arquivos = $pastaAtual ? $pastaAtual->arquivos : Arquivo::whereNull('pasta_id')->orderBy('nome_original')->get();
        $sectors = Sector::orderBy('name')->get();
        $breadcrumb = $pastaAtual ? $pastaAtual->breadcrumb() : collect();

        return view('repositorio.index', compact('pastaAtual', 'subpastas', 'arquivos', 'sectors', 'breadcrumb'));
    }

    public function storePasta(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:pastas,id',
            'sector_id' => 'required|exists:sectors,id',
            'is_private' => 'boolean',
        ]);
        $validated['is_private'] = $request->boolean('is_private');

        Pasta::create($validated);

        return redirect()->back()->with('status', 'Pasta criada com sucesso.');
    }

    public function editPasta(Pasta $pasta)
    {
        $sectors = Sector::orderBy('name')->get();
        return view('repositorio.editar-pasta', compact('pasta', 'sectors'));
    }

    public function updatePasta(Request $request, Pasta $pasta)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'sector_id' => 'required|exists:sectors,id',
            'is_private' => 'boolean',
        ]);
        $validated['is_private'] = $request->boolean('is_private');

        $pasta->update($validated);

        return redirect()->route('repositorio.index', $pasta->parent_id ? ['pasta' => $pasta->parent_id] : [])
            ->with('status', 'Pasta atualizada com sucesso.');
    }

    public function destroyPasta(Pasta $pasta)
    {
        $parentId = $pasta->parent_id;

        foreach ($pasta->todosArquivosDescendentes() as $arquivo) {
            Storage::disk('arquivos')->delete($arquivo->caminho);
        }

        $pasta->delete();

        return redirect()->route('repositorio.index', $parentId ? ['pasta' => $parentId] : [])
            ->with('status', 'Pasta removida.');
    }

    public function storeArquivo(Request $request)
    {
        $validated = $request->validate([
            'pasta_id' => 'nullable|exists:pastas,id',
            'arquivo' => 'required|file|max:51200',
            'descricao' => 'nullable|string',
            'sector_id' => 'required|exists:sectors,id',
            'is_private' => 'boolean',
        ]);

        $file = $request->file('arquivo');
        $sector = Sector::find($validated['sector_id']);

        if ($sector->quotaExcedida($file->getSize())) {
            return back()->withErrors([
                'arquivo' => "O setor \"{$sector->name}\" atingiria a cota de armazenamento ({$sector->quotaFormatada()}) com este envio. Uso atual: {$sector->usoFormatado()}.",
            ])->withInput();
        }

        $caminho = $file->store('uploads', 'arquivos');

        Arquivo::create([
            'pasta_id' => $validated['pasta_id'] ?? null,
            'nome_original' => $file->getClientOriginalName(),
            'caminho' => $caminho,
            'extensao' => strtolower($file->getClientOriginalExtension()),
            'tamanho' => $file->getSize(),
            'descricao' => $validated['descricao'] ?? null,
            'sector_id' => $validated['sector_id'] ?? null,
            'is_private' => $request->boolean('is_private'),
        ]);

        return redirect()->back()->with('status', 'Arquivo enviado com sucesso.');
    }

    public function download(Arquivo $arquivo)
    {
        return Storage::disk('arquivos')->download($arquivo->caminho, $arquivo->nome_original);
    }

    public function editArquivo(Arquivo $arquivo)
    {
        $sectors = Sector::orderBy('name')->get();
        return view('repositorio.editar-arquivo', compact('arquivo', 'sectors'));
    }

    public function updateArquivo(Request $request, Arquivo $arquivo)
    {
        $validated = $request->validate([
            'descricao' => 'nullable|string',
            'sector_id' => 'required|exists:sectors,id',
            'is_private' => 'boolean',
        ]);
        $validated['is_private'] = $request->boolean('is_private');

        $arquivo->update($validated);

        return redirect()->route('repositorio.index', $arquivo->pasta_id ? ['pasta' => $arquivo->pasta_id] : [])
            ->with('status', 'Arquivo atualizado com sucesso.');
    }

    public function destroyArquivo(Arquivo $arquivo)
    {
        $pastaId = $arquivo->pasta_id;

        Storage::disk('arquivos')->delete($arquivo->caminho);
        $arquivo->delete();

        return redirect()->route('repositorio.index', $pastaId ? ['pasta' => $pastaId] : [])
            ->with('status', 'Arquivo removido.');
    }
}
