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
            'unidade' => 'nullable|string|max:100',
            'telefone' => 'required|string|max:100',
            'sector_id' => 'required|exists:sectors,id',
            'email' => 'nullable|email|max:100',
            'telefone_externo' => 'nullable|string|max:100',
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
            'unidade' => 'nullable|string|max:100',
            'telefone' => 'required|string|max:100',
            'sector_id' => 'required|exists:sectors,id',
            'email' => 'nullable|email|max:100',
            'telefone_externo' => 'nullable|string|max:100',
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
        $csv = "ramal,unidade,setor,nome,cargo,email,telefone_externo\n";
        $csv .= "2222,CETEM-RJ,TI,Fulano de Tal,Analista,fulano@cetem.gov.br,(21)3512-0000\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_ramais.csv"',
        ]);
    }

    public function loteImport(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt,xlsx']);

        $linhas = $this->lerLinhasDaPlanilha($request->file('arquivo'));

        if (empty($linhas)) {
            return redirect()->route('telefones.lote.form')
                ->with('status', '0 ramal(is) importado(s) com sucesso.')
                ->with('erros_lote', ['Arquivo vazio ou sem linhas de dados.']);
        }

        $header = array_map([$this, 'normalizarCabecalho'], array_shift($linhas));

        $sucesso = 0;
        $erros = [];
        $linhaNum = 1;

        foreach ($linhas as $row) {
            $linhaNum++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = array_pad($row, count($header), null);
            $dados = array_combine($header, $row);
            $dados = array_map(fn ($v) => trim((string) $v), $dados);

            $nome = $dados['nome'] ?? '';
            $telefone = $dados['telefone'] ?? '';
            $setorNome = $dados['setor'] ?? '';

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
                'unidade' => ($dados['unidade'] ?? '') ?: null,
                'telefone' => $telefone,
                'sector_id' => $sector->id,
                'email' => ($dados['email'] ?? '') ?: null,
                'telefone_externo' => ($dados['telefone_externo'] ?? '') ?: null,
                'cargo' => ($dados['cargo'] ?? '') ?: null,
            ]);

            $sucesso++;
        }

        return redirect()->route('telefones.lote.form')
            ->with('status', "{$sucesso} ramal(is) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }

    /**
     * Lê um arquivo CSV/TXT ou XLSX e devolve as linhas (a primeira é o cabeçalho),
     * descartando linhas totalmente em branco.
     */
    private function lerLinhasDaPlanilha($file): array
    {
        if (strtolower($file->getClientOriginalExtension()) === 'xlsx') {
            $planilha = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $linhas = $planilha->getActiveSheet()->toArray(null, true, true, false);
        } else {
            $conteudo = file_get_contents($file->getRealPath());
            $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
            $textoLinhas = preg_split('/\r\n|\r|\n/', $conteudo);
            $linhas = array_map('str_getcsv', $textoLinhas);
        }

        return array_values(array_filter(
            $linhas,
            fn ($linha) => count(array_filter($linha, fn ($v) => trim((string) $v) !== '')) > 0
        ));
    }

    /**
     * Normaliza cabeçalhos vindos tanto do modelo simples quanto do catálogo
     * telefônico oficial (que usa "Ramal", "E-mail", "Telefone Externo" etc,
     * às vezes com espaços extras).
     */
    private function normalizarCabecalho($valor): string
    {
        $valor = mb_strtolower(trim((string) $valor));
        $valor = str_replace(['-', '_'], ' ', $valor);
        $valor = preg_replace('/\s+/', ' ', $valor);

        return match ($valor) {
            'ramal', 'telefone' => 'telefone',
            'e mail' => 'email',
            'telefone externo' => 'telefone_externo',
            default => $valor,
        };
    }
}
