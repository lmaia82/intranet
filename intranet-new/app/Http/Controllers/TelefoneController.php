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
        $letras = Telefone::pluck('nome')
            ->map(fn ($nome) => mb_strtoupper(mb_substr($nome, 0, 1)))
            ->unique()
            ->sort()
            ->values();

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

    public function loteForm()
    {
        return view('telefones.lote');
    }

    public function loteTemplate()
    {
        $csv = "nome,telefone,setor,email,cargo\n";
        $csv .= "Fulano de Tal,2222,TI,fulano@cetem.gov.br,Analista\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_ramais.csv"',
        ]);
    }

    public function loteImport(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt']);

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

            $nome = trim($dados['nome'] ?? '');
            $telefone = trim($dados['telefone'] ?? '');
            $setorNome = trim($dados['setor'] ?? '');

            if ($nome === '' || $telefone === '' || $setorNome === '') {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios em branco.";
                continue;
            }

            $sector = Sector::whereRaw('LOWER(name) = ?', [mb_strtolower($setorNome)])->first();

            if (!$sector) {
                $erros[] = "Linha {$linhaNum}: setor '{$setorNome}' não encontrado. Cadastre-o antes em Administração > Setores.";
                continue;
            }

            Telefone::create([
                'nome' => $nome,
                'telefone' => $telefone,
                'sector_id' => $sector->id,
                'email' => trim($dados['email'] ?? '') ?: null,
                'cargo' => trim($dados['cargo'] ?? '') ?: null,
            ]);

            $sucesso++;
        }

        return redirect()->route('telefones.lote.form')
            ->with('status', "{$sucesso} ramal(is) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }
}
