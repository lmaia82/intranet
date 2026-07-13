<?php

namespace App\Http\Controllers;

use App\Models\EventoGravado;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventoGravadoController extends Controller
{
    public function create()
    {
        return view('eventos-gravados.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'data' => 'required|date',
            'youtube_url' => 'required|url|max:255',
        ]);

        EventoGravado::create($validated);

        return redirect()->route('eventos.index')->with('status', 'Evento gravado cadastrado com sucesso.');
    }

    public function edit(EventoGravado $eventoGravado)
    {
        return view('eventos-gravados.edit', compact('eventoGravado'));
    }

    public function update(Request $request, EventoGravado $eventoGravado)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'data' => 'required|date',
            'youtube_url' => 'required|url|max:255',
        ]);

        $eventoGravado->update($validated);

        return redirect()->route('eventos.index')->with('status', 'Evento gravado atualizado com sucesso.');
    }

    public function destroy(EventoGravado $eventoGravado)
    {
        $eventoGravado->delete();
        return redirect()->route('eventos.index')->with('status', 'Evento gravado removido.');
    }

    public function loteForm()
    {
        return view('eventos-gravados.lote');
    }

    public function loteTemplate()
    {
        $csv = "titulo,data,youtube_url\n";
        $csv .= "Exemplo de evento gravado,31/12/2025,https://www.youtube.com/watch?v=exemplo\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_eventos_gravados.csv"',
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

            EventoGravado::create([
                'titulo' => $titulo,
                'data' => $data,
                'youtube_url' => $url,
            ]);

            $sucesso++;
        }

        return redirect()->route('eventos-gravados.lote.form')
            ->with('status', "{$sucesso} evento(s) gravado(s) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }
}
