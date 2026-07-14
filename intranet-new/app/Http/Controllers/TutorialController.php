<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function index()
    {
        $tutoriais = Tutorial::orderBy('data', 'desc')->paginate(10);

        return view('tutoriais.index', compact('tutoriais'));
    }

    public function create()
    {
        return view('tutoriais.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'data' => 'required|date',
            'youtube_url' => 'required|url|max:255',
        ]);

        Tutorial::create($validated);

        return redirect()->route('tutoriais.index')->with('status', 'Tutorial cadastrado com sucesso.');
    }

    public function edit(Tutorial $tutorial)
    {
        return view('tutoriais.edit', compact('tutorial'));
    }

    public function update(Request $request, Tutorial $tutorial)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'data' => 'required|date',
            'youtube_url' => 'required|url|max:255',
        ]);

        $tutorial->update($validated);

        return redirect()->route('tutoriais.index')->with('status', 'Tutorial atualizado com sucesso.');
    }

    public function destroy(Tutorial $tutorial)
    {
        $tutorial->delete();
        return redirect()->route('tutoriais.index')->with('status', 'Tutorial removido.');
    }

    public function loteForm()
    {
        return view('tutoriais.lote');
    }

    public function loteTemplate()
    {
        $csv = "titulo,data,youtube_url\n";
        $csv .= "Exemplo de tutorial,31/12/2025,https://www.youtube.com/watch?v=exemplo\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_tutoriais.csv"',
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

            $titulo = trim($dados['titulo'] ?? '');
            $dataTexto = trim($dados['data'] ?? '');
            $url = trim($dados['youtube_url'] ?? '');

            if ($titulo === '' || $dataTexto === '' || $url === '') {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios em branco.";
                continue;
            }

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

            if (!str_contains($url, 'youtube.com') && !str_contains($url, 'youtu.be')) {
                $erros[] = "Linha {$linhaNum}: URL '{$url}' não parece ser do YouTube.";
                continue;
            }

            Tutorial::create([
                'titulo' => $titulo,
                'data' => $data,
                'youtube_url' => $url,
            ]);

            $sucesso++;
        }

        return redirect()->route('tutoriais.lote.form')
            ->with('status', "{$sucesso} tutorial(is) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }
}
