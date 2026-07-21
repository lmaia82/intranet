<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Destaque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'inicio_em' => 'required|date',
            'fim_em' => 'required|date|after:inicio_em',
        ]);

        unset($validated['imagem']);
        $validated['ativo'] = $request->boolean('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $sector = auth()->user()->sector;
        abort_unless($sector, 422, 'Você precisa estar vinculado a um setor (lotação) para cadastrar destaques. Atualize seu perfil.');

        $validated['sector_id'] = $sector->id;
        $validated['arquivo_id'] = $this->salvarImagem($request->file('imagem'), $sector);

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
            'inicio_em' => 'required|date',
            'fim_em' => 'required|date|after:inicio_em',
        ]);

        unset($validated['imagem']);
        $validated['ativo'] = $request->boolean('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        if ($request->hasFile('imagem')) {
            $sector = auth()->user()->sector;
            abort_unless($sector, 422, 'Você precisa estar vinculado a um setor (lotação) para atualizar destaques. Atualize seu perfil.');

            $this->removerImagem($destaque);
            $validated['sector_id'] = $sector->id;
            $validated['arquivo_id'] = $this->salvarImagem($request->file('imagem'), $sector);
        }

        $destaque->update($validated);

        return redirect()->route('destaques.index')->with('status', 'Destaque atualizado com sucesso.');
    }

    public function destroy(Destaque $destaque)
    {
        $this->removerImagem($destaque);
        $destaque->delete();
        return redirect()->route('destaques.index')->with('status', 'Destaque removido.');
    }

    /**
     * Remove do MinIO e do Repositório a imagem atualmente associada ao
     * destaque, para não deixar arquivo órfão ao excluir ou substituir a
     * imagem.
     */
    private function removerImagem(Destaque $destaque): void
    {
        if ($destaque->arquivo) {
            Storage::disk('arquivos')->delete($destaque->arquivo->caminho);
            $destaque->arquivo->delete();
        }
    }

    /**
     * Salva a imagem do destaque no MinIO (disco "arquivos"), na pasta
     * "Destaques" do setor do usuário logado, e registra o arquivo no
     * Repositório. Retorna o id do Arquivo criado.
     */
    private function salvarImagem($file, $sector): int
    {
        $pasta = $sector->pastaDestaques();
        $caminho = $file->store('uploads', 'arquivos');

        $arquivo = Arquivo::create([
            'pasta_id' => $pasta->id,
            'criado_por_id' => auth()->id(),
            'nome_original' => $file->getClientOriginalName(),
            'caminho' => $caminho,
            'extensao' => strtolower($file->getClientOriginalExtension()),
            'tamanho' => $file->getSize(),
            'sector_id' => $sector->id,
            'is_private' => false,
        ]);

        return $arquivo->id;
    }
}
