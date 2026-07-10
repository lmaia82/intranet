<?php

namespace App\Http\Controllers;

use App\Models\Artigo;
use Illuminate\Http\Request;

class ArtigoController extends Controller
{
    public function index(Request $request)
    {
        $query = Artigo::query()->latest('ano');

        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }
        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }
        if ($request->filled('autores')) {
            $query->where('autores', 'like', '%' . $request->autores . '%');
        }
        if ($request->filled('palavra_chave')) {
            $query->where('palavras_chave', 'like', '%' . $request->palavra_chave . '%');
        }

        $artigos = $query->paginate(15)->withQueryString();

        return view('artigos.index', compact('artigos'));
    }

    public function show(Artigo $artigo)
    {
        return view('artigos.show', compact('artigo'));
    }

    public function create()
    {
        return view('artigos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'ano' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'autores' => 'required|string|max:255',
            'palavras_chave' => 'nullable|string|max:255',
            'arquivo' => 'required|file|mimes:pdf|max:20480',
        ]);

        $validated['arquivo'] = $request->file('arquivo')->store('artigos', 'public');

        Artigo::create($validated);

        return redirect()->route('artigos.index')->with('status', 'Artigo cadastrado com sucesso.');
    }

    public function edit(Artigo $artigo)
    {
        return view('artigos.edit', compact('artigo'));
    }

    public function update(Request $request, Artigo $artigo)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'ano' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'autores' => 'required|string|max:255',
            'palavras_chave' => 'nullable|string|max:255',
            'arquivo' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        if ($request->hasFile('arquivo')) {
            $validated['arquivo'] = $request->file('arquivo')->store('artigos', 'public');
        }

        $artigo->update($validated);

        return redirect()->route('artigos.index')->with('status', 'Artigo atualizado com sucesso.');
    }

    public function destroy(Artigo $artigo)
    {
        $artigo->delete();
        return redirect()->route('artigos.index')->with('status', 'Artigo removido.');
    }

    public function loteForm()
    {
        return view('artigos.lote');
    }

    public function loteTemplate()
    {
        $csv = "titulo,ano,autores,palavras_chave,arquivo\n";
        $csv .= "Exemplo de titulo do artigo,2024,\"Fulano de Tal, Ciclana Silva\",\"mineracao, sustentabilidade\",exemplo.pdf\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_artigos.csv"',
        ]);
    }

    public function loteImport(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
            'pdfs' => 'required|array',
            'pdfs.*' => 'file|mimes:pdf|max:20480',
        ]);

        $pdfsPorNome = [];
        foreach ($request->file('pdfs', []) as $pdf) {
            $pdfsPorNome[trim($pdf->getClientOriginalName())] = $pdf;
        }

        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
        $linhas = preg_split('/\r\n|\r|\n/', $conteudo);
        $linhas = array_values(array_filter($linhas, fn($l) => trim($l) !== ''));

        $header = array_map('trim', str_getcsv(array_shift($linhas)));

        $sucesso = 0;
        $erros = [];
        $linhaNum = 1;

        foreach ($linhas as $linhaTexto) {
            $linhaNum++;
            $row = array_map('trim', str_getcsv($linhaTexto));
            $dados = array_combine($header, $row);

            $nomeArquivo = trim($dados['arquivo'] ?? '');

            if (empty($dados['titulo']) || empty($dados['ano']) || empty($dados['autores']) || empty($nomeArquivo)) {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios em branco.";
                continue;
            }

            if (!isset($pdfsPorNome[$nomeArquivo])) {
                $erros[] = "Linha {$linhaNum}: PDF '{$nomeArquivo}' não foi enviado junto. Recebidos: " . implode(', ', array_keys($pdfsPorNome));
                continue;
            }

            $caminho = $pdfsPorNome[$nomeArquivo]->store('artigos', 'public');

            Artigo::create([
                'titulo' => $dados['titulo'],
                'ano' => (int) $dados['ano'],
                'autores' => $dados['autores'],
                'palavras_chave' => $dados['palavras_chave'] ?? null,
                'arquivo' => $caminho,
            ]);

            $sucesso++;
        }

        return redirect()->route('artigos.lote.form')
            ->with('status', "{$sucesso} artigo(s) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }
}
