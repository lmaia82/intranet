<?php

namespace App\Http\Controllers;

use App\Models\Acesso;
use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\Sector;
use App\Services\PaperlessService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RepositorioController extends Controller
{
    public function __construct(private PaperlessService $paperless)
    {
    }

    public function index(?Pasta $pasta = null)
    {
        $user = auth()->user();
        abort_if($pasta && !$pasta->visivelPara($user), 403, 'Você não tem acesso a esta pasta.');

        $pastaAtual = $pasta;
        $subpastas = ($pastaAtual ? $pastaAtual->children : Pasta::whereNull('parent_id')->orderBy('nome')->get())
            ->filter(fn (Pasta $p) => $p->visivelPara($user))
            ->values()
            ->load('sector');
        $arquivos = ($pastaAtual ? $pastaAtual->arquivos : Arquivo::whereNull('pasta_id')->orderBy('nome_original')->get())
            ->each(fn (Arquivo $a) => $pastaAtual ? $a->setRelation('pasta', $pastaAtual) : null)
            ->filter(fn (Arquivo $a) => $a->visivelPara($user))
            ->values()
            ->load(['sector', 'criadoPor']);

        $arquivos->where('ocr_status', 'pendente')->each(fn (Arquivo $a) => $this->paperless->sincronizarPendente($a));

        $sectors = Sector::orderBy('sigla')->get();
        $breadcrumb = $pastaAtual ? $pastaAtual->breadcrumb() : collect();

        $pastasParaSelecao = $this->pastasParaSelecao($user);

        $todasPastasVisiveis = Pasta::orderBy('nome')->get()->filter(fn (Pasta $p) => $p->visivelPara($user));
        $arvorePastas = $this->construirArvore($todasPastasVisiveis, null);
        $pastasAbertas = $breadcrumb->pluck('id')->toArray();

        return view('repositorio.index', compact(
            'pastaAtual', 'subpastas', 'arquivos', 'sectors', 'breadcrumb', 'pastasParaSelecao',
            'arvorePastas', 'pastasAbertas'
        ));
    }

    private function pastasParaSelecao($user)
    {
        return Pasta::orderBy('nome')->get()
            ->filter(fn (Pasta $p) => $p->visivelPara($user))
            ->map(fn (Pasta $p) => ['id' => $p->id, 'caminho' => $p->breadcrumb()->pluck('nome')->implode(' / ')])
            ->sortBy('caminho')
            ->values();
    }

    private function construirArvore($pastas, ?int $parentId)
    {
        return $pastas->where('parent_id', $parentId)
            ->map(fn (Pasta $p) => [
                'id' => $p->id,
                'nome' => $p->nome,
                'filhas' => $this->construirArvore($pastas, $p->id),
            ])
            ->values();
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
        $sectors = Sector::orderBy('sigla')->get();
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
            'pasta_id' => 'required|exists:pastas,id',
            'arquivo' => 'required|file|max:51200',
            'descricao' => 'nullable|string',
            'data' => 'nullable|date',
            'sector_id' => 'required|exists:sectors,id',
            'is_private' => 'boolean',
        ]);

        $file = $request->file('arquivo');
        $sector = Sector::find($validated['sector_id']);

        $pastaDestino = Pasta::find($validated['pasta_id']);
        abort_unless($pastaDestino->visivelPara(auth()->user()), 403, 'Você não tem acesso a essa pasta.');

        if ($sector->quotaExcedida($file->getSize())) {
            return back()->withErrors([
                'arquivo' => "O setor \"{$sector->sigla}\" atingiria a cota de armazenamento ({$sector->quotaFormatada()}) com este envio. Uso atual: {$sector->usoFormatado()}.",
            ])->withInput();
        }

        $caminho = $file->store('uploads', 'arquivos');

        $arquivo = Arquivo::create([
            'pasta_id' => $validated['pasta_id'],
            'criado_por_id' => auth()->id(),
            'nome_original' => $file->getClientOriginalName(),
            'caminho' => $caminho,
            'extensao' => strtolower($file->getClientOriginalExtension()),
            'tamanho' => $file->getSize(),
            'descricao' => $validated['descricao'] ?? null,
            'data' => $validated['data'] ?? now()->toDateString(),
            'sector_id' => $validated['sector_id'] ?? null,
            'is_private' => $request->boolean('is_private'),
        ]);

        $status = 'Arquivo enviado com sucesso.';
        if ($this->paperless->enviarParaOcr($arquivo)) {
            $status .= ' O reconhecimento de texto (OCR) está sendo processado e pode levar alguns instantes — acompanhe o status na listagem.';
        }

        return redirect()->back()->with('status', $status);
    }

    public function download(Arquivo $arquivo)
    {
        abort_unless($arquivo->visivelPara(auth()->user()), 403, 'Você não tem acesso a este arquivo.');

        Acesso::create([
            'user_id' => auth()->id(),
            'modulo' => 'repositorio',
            'referencia_tipo' => 'arquivo',
            'referencia_id' => $arquivo->id,
        ]);

        return Storage::disk('arquivos')->download($arquivo->caminho, $arquivo->nome_original);
    }

    public function visualizar(Arquivo $arquivo)
    {
        abort_unless($arquivo->visivelPara(auth()->user()), 403, 'Você não tem acesso a este arquivo.');

        return Storage::disk('arquivos')->response($arquivo->caminho);
    }

    public function ocrStatus(Arquivo $arquivo)
    {
        abort_unless($arquivo->visivelPara(auth()->user()), 403, 'Você não tem acesso a este arquivo.');

        if ($arquivo->ocr_status === 'pendente') {
            $this->paperless->sincronizarPendente($arquivo);
            $arquivo->refresh();
        }

        return response()->json([
            'status' => $arquivo->ocr_status,
            'erro' => $arquivo->ocr_erro,
        ]);
    }

    public function editArquivo(Arquivo $arquivo)
    {
        $sectors = Sector::orderBy('sigla')->get();
        return view('repositorio.editar-arquivo', compact('arquivo', 'sectors'));
    }

    public function updateArquivo(Request $request, Arquivo $arquivo)
    {
        $validated = $request->validate([
            'descricao' => 'nullable|string',
            'data' => 'nullable|date',
            'sector_id' => 'required|exists:sectors,id',
            'is_private' => 'boolean',
        ]);
        $validated['is_private'] = $request->boolean('is_private');

        $arquivo->update($validated);

        return redirect()->route('repositorio.index', $arquivo->pasta_id ? ['pasta' => $arquivo->pasta_id] : [])
            ->with('status', 'Arquivo atualizado com sucesso.');
    }

    public function moverArquivoForm(Arquivo $arquivo)
    {
        abort_unless($arquivo->visivelPara(auth()->user()), 403, 'Você não tem acesso a este arquivo.');

        $pastasParaSelecao = $this->pastasParaSelecao(auth()->user());
        return view('repositorio.mover-arquivo', compact('arquivo', 'pastasParaSelecao'));
    }

    public function moverArquivo(Request $request, Arquivo $arquivo)
    {
        abort_unless($arquivo->visivelPara(auth()->user()), 403, 'Você não tem acesso a este arquivo.');

        $validated = $request->validate([
            'pasta_id' => 'required|exists:pastas,id',
        ]);

        $pastaDestino = Pasta::find($validated['pasta_id']);
        abort_unless($pastaDestino->visivelPara(auth()->user()), 403, 'Você não tem acesso a essa pasta.');

        $arquivo->update(['pasta_id' => $pastaDestino->id]);

        return redirect()->route('repositorio.index', ['pasta' => $pastaDestino->id])
            ->with('status', 'Arquivo movido com sucesso.');
    }

    public function destroyArquivo(Arquivo $arquivo)
    {
        $pastaId = $arquivo->pasta_id;

        Storage::disk('arquivos')->delete($arquivo->caminho);
        $arquivo->delete();

        return redirect()->route('repositorio.index', $pastaId ? ['pasta' => $pastaId] : [])
            ->with('status', 'Arquivo removido.');
    }

    public function loteArquivosForm()
    {
        return view('repositorio.arquivos-lote');
    }

    public function loteArquivosTemplate()
    {
        $csv = "arquivo,pasta,setor,data,publico,descricao\n";
        $csv .= "exemplo.pdf,Notas Fiscais/2024,Financeiro,31/12/2024,nao,Nota fiscal de exemplo\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_arquivos_lote.csv"',
        ]);
    }

    public function loteArquivosImport(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
            'arquivos' => 'required|array',
            'arquivos.*' => 'file|max:51200',
        ]);

        $arquivosPorNome = collect($request->file('arquivos'))
            ->keyBy(fn ($file) => $file->getClientOriginalName());

        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
        $linhas = preg_split('/\r\n|\r|\n/', $conteudo);
        $linhas = array_values(array_filter($linhas, fn ($l) => trim($l) !== ''));

        $header = array_map('trim', str_getcsv(array_shift($linhas)));

        $sucesso = 0;
        $enviadosParaOcr = 0;
        $erros = [];
        $linhaNum = 1;
        $pastasCriadas = [];

        foreach ($linhas as $linhaTexto) {
            $linhaNum++;
            $row = array_map('trim', str_getcsv($linhaTexto));
            $dados = array_combine($header, $row);

            $nomeArquivo = trim($dados['arquivo'] ?? '');
            $setorNome = trim($dados['setor'] ?? '');
            $pastaTexto = trim($dados['pasta'] ?? '');

            if ($nomeArquivo === '' || $setorNome === '' || $pastaTexto === '') {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios em branco (arquivo, setor e pasta são obrigatórios).";
                continue;
            }

            $file = $arquivosPorNome->get($nomeArquivo);
            if (!$file) {
                $erros[] = "Linha {$linhaNum}: arquivo '{$nomeArquivo}' não foi enviado junto com o lote.";
                continue;
            }

            $sector = Sector::whereRaw('LOWER(sigla) = ?', [strtolower($setorNome)])->first();
            if (!$sector) {
                $erros[] = "Linha {$linhaNum}: setor '{$setorNome}' não encontrado.";
                continue;
            }

            if ($sector->quotaExcedida($file->getSize())) {
                $erros[] = "Linha {$linhaNum}: o setor \"{$sector->sigla}\" atingiria a cota de armazenamento com este arquivo.";
                continue;
            }

            $dataTexto = trim($dados['data'] ?? '');
            $data = now()->toDateString();
            if ($dataTexto !== '') {
                $data = null;
                foreach (['d/m/Y', 'Y-m-d'] as $formato) {
                    try {
                        $data = Carbon::createFromFormat($formato, $dataTexto)->startOfDay();
                        break;
                    } catch (\Exception $e) {
                        $data = null;
                    }
                }

                if (!$data) {
                    $erros[] = "Linha {$linhaNum}: data '{$dataTexto}' inválida (use dd/mm/aaaa).";
                    continue;
                }
            }

            $publicoTexto = strtolower(trim($dados['publico'] ?? 'sim'));
            $isPrivate = !in_array($publicoTexto, ['sim', 's', 'yes', '1', 'publico', 'público'], true);

            $pastaId = null;
            foreach (explode('/', $pastaTexto) as $segmento) {
                $segmento = trim($segmento);
                if ($segmento === '') {
                    continue;
                }

                $chave = $sector->id . ':' . $pastaId . ':' . mb_strtolower($segmento);
                if (!isset($pastasCriadas[$chave])) {
                    $pastasCriadas[$chave] = Pasta::firstOrCreate(
                        ['nome' => $segmento, 'parent_id' => $pastaId, 'sector_id' => $sector->id],
                        ['is_private' => false]
                    );
                }

                $pastaId = $pastasCriadas[$chave]->id;
            }

            $caminho = $file->store('uploads', 'arquivos');

            $arquivo = Arquivo::create([
                'pasta_id' => $pastaId,
                'criado_por_id' => auth()->id(),
                'nome_original' => $file->getClientOriginalName(),
                'caminho' => $caminho,
                'extensao' => strtolower($file->getClientOriginalExtension()),
                'tamanho' => $file->getSize(),
                'descricao' => trim($dados['descricao'] ?? '') ?: null,
                'data' => $data,
                'sector_id' => $sector->id,
                'is_private' => $isPrivate,
            ]);

            if ($this->paperless->enviarParaOcr($arquivo)) {
                $enviadosParaOcr++;
            }

            $sucesso++;
        }

        $status = "{$sucesso} arquivo(s) importado(s) com sucesso.";
        if ($enviadosParaOcr > 0) {
            $status .= " {$enviadosParaOcr} PDF(s) enviado(s) para reconhecimento de texto (OCR) em segundo plano.";
        }

        return redirect()->route('repositorio.arquivos.lote.form')
            ->with('status', $status)
            ->with('erros_lote', $erros);
    }
}
