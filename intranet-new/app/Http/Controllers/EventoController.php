<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoGravado;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        $proximos = Evento::where('dt_start', '>=', now()->toDateString())->orderBy('dt_start')->get();
        $anteriores = Evento::where('dt_start', '<', now()->toDateString())->orderBy('dt_start', 'desc')->paginate(10);
        $gravados = EventoGravado::orderBy('data', 'desc')->get();

        return view('eventos.index', compact('proximos', 'anteriores', 'gravados'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'local' => 'required|string|max:100',
            'informacoes' => 'nullable|string',
            'dt_start' => 'required|date',
            'dt_end' => 'nullable|date|after_or_equal:dt_start',
            'tm_start' => 'nullable',
            'tm_end' => 'nullable',
        ]);

        Evento::create($validated);

        return redirect()->route('eventos.index')->with('status', 'Evento cadastrado com sucesso.');
    }

    public function edit(Evento $evento)
    {
        return view('eventos.edit', compact('evento'));
    }

    public function update(Request $request, Evento $evento)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'local' => 'required|string|max:100',
            'informacoes' => 'nullable|string',
            'dt_start' => 'required|date',
            'dt_end' => 'nullable|date|after_or_equal:dt_start',
            'tm_start' => 'nullable',
            'tm_end' => 'nullable',
        ]);

        $evento->update($validated);

        return redirect()->route('eventos.index')->with('status', 'Evento atualizado com sucesso.');
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();
        return redirect()->route('eventos.index')->with('status', 'Evento removido.');
    }

    public function loteForm()
    {
        return view('eventos.lote');
    }

    public function loteTemplate()
    {
        $csv = "title,local,dt_start,dt_end,tm_start,tm_end,informacoes\n";
        $csv .= "Reunião de planejamento,Sala 3,31/12/2025,,09:00,10:00,Pauta de exemplo\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_eventos.csv"',
        ]);
    }

    public function loteImport(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt']);

        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
        $linhas = preg_split('/\r\n|\r|\n/', $conteudo);
        $linhas = array_values(array_filter($linhas, fn ($l) => trim($l) !== ''));

        $header = array_map('trim', str_getcsv(array_shift($linhas)));

        $sucesso = 0;
        $erros = [];
        $linhaNum = 1;

        foreach ($linhas as $linhaTexto) {
            $linhaNum++;
            $row = array_map('trim', str_getcsv($linhaTexto));
            $dados = array_combine($header, $row);

            $title = trim($dados['title'] ?? '');
            $local = trim($dados['local'] ?? '');
            $dtStartTexto = trim($dados['dt_start'] ?? '');

            if ($title === '' || $local === '' || $dtStartTexto === '') {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios em branco.";
                continue;
            }

            $dtStart = $this->parseData($dtStartTexto);
            if (!$dtStart) {
                $erros[] = "Linha {$linhaNum}: data início '{$dtStartTexto}' inválida (use dd/mm/aaaa).";
                continue;
            }

            $dtEnd = null;
            $dtEndTexto = trim($dados['dt_end'] ?? '');
            if ($dtEndTexto !== '') {
                $dtEnd = $this->parseData($dtEndTexto);
                if (!$dtEnd) {
                    $erros[] = "Linha {$linhaNum}: data fim '{$dtEndTexto}' inválida (use dd/mm/aaaa).";
                    continue;
                }
                if ($dtEnd->lt($dtStart)) {
                    $erros[] = "Linha {$linhaNum}: data fim é anterior à data início.";
                    continue;
                }
            }

            Evento::create([
                'title' => $title,
                'local' => $local,
                'informacoes' => trim($dados['informacoes'] ?? '') ?: null,
                'dt_start' => $dtStart,
                'dt_end' => $dtEnd,
                'tm_start' => trim($dados['tm_start'] ?? '') ?: null,
                'tm_end' => trim($dados['tm_end'] ?? '') ?: null,
            ]);

            $sucesso++;
        }

        return redirect()->route('eventos.lote.form')
            ->with('status', "{$sucesso} evento(s) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }

    private function parseData(string $texto): ?Carbon
    {
        foreach (['d/m/Y', 'Y-m-d'] as $formato) {
            try {
                return Carbon::createFromFormat($formato, $texto)->startOfDay();
            } catch (\Exception $e) {
                // tenta o próximo formato
            }
        }

        return null;
    }
}
