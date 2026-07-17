<?php

namespace App\Http\Controllers;

use App\Mail\NovoInformativoMail;
use App\Models\Acesso;
use App\Models\Informativo;
use App\Models\InformativoEnvio;
use App\Models\Sector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InformativoController extends Controller
{
    public function index(Request $request)
    {
        $query = Informativo::with('sector')->latest('published_at');

        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        $informativos = $query->paginate(10)->withQueryString();
        $sectors = Sector::orderBy('sigla')->get();

        return view('informativos.index', compact('informativos', 'sectors'));
    }

    public function show(Informativo $informativo)
    {
        $informativo->load('sector', 'envios');

        if (auth()->check()) {
            Acesso::create([
                'user_id' => auth()->id(),
                'modulo' => 'informativos',
                'referencia_tipo' => 'informativo',
                'referencia_id' => $informativo->id,
            ]);
        }

        return view('informativos.show', compact('informativo'));
    }

    public function create()
    {
        $sectors = Sector::orderBy('sigla')->get();
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

        $informativo = Informativo::create($validated);

        $status = 'Informativo publicado com sucesso.';

        if ($request->boolean('notificar_email')) {
            $enviados = $this->enviarNotificacoes($informativo);
            $status .= " E-mail enviado para {$enviados} destinatário(s).";
        }

        return redirect()->route('informativos.index')->with('status', $status);
    }

    public function edit(Informativo $informativo)
    {
        $sectors = Sector::orderBy('sigla')->get();
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

    public function loteForm()
    {
        return view('informativos.lote');
    }

    public function loteTemplate()
    {
        $csv = "title,content,setor,publico,data_publicacao\n";
        $csv .= "Exemplo de informativo,\"Conteúdo do informativo.\nPode ter várias linhas, desde que o texto fique entre aspas.\",,sim,31/12/2025\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_informativos.csv"',
        ]);
    }

    public function loteImport(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt']);

        $linhas = $this->lerLinhasDoCsv($request->file('csv')->getRealPath());

        if (empty($linhas)) {
            return redirect()->route('informativos.lote.form')
                ->with('status', '0 informativo(s) importado(s) com sucesso.')
                ->with('erros_lote', ['Arquivo vazio ou sem linhas de dados.']);
        }

        $header = array_map(fn ($h) => mb_strtolower(trim((string) $h)), array_shift($linhas));

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

            $title = $dados['title'] ?? '';
            $content = $dados['content'] ?? '';

            if ($title === '' || $content === '') {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios (title, content) em branco.";
                continue;
            }

            $sectorId = null;
            $setorNome = $dados['setor'] ?? '';
            if ($setorNome !== '') {
                $sector = Sector::whereRaw('LOWER(sigla) = ?', [mb_strtolower($setorNome)])->first();

                if (!$sector) {
                    $erros[] = "Linha {$linhaNum}: setor '{$setorNome}' não encontrado.";
                    continue;
                }

                $sectorId = $sector->id;
            }

            $dataTexto = $dados['data_publicacao'] ?? '';
            $publishedAt = now();
            if ($dataTexto !== '') {
                $publishedAt = null;
                foreach (['d/m/Y', 'Y-m-d'] as $formato) {
                    try {
                        $publishedAt = Carbon::createFromFormat($formato, $dataTexto)->startOfDay();
                        break;
                    } catch (\Exception $e) {
                        $publishedAt = null;
                    }
                }

                if (!$publishedAt) {
                    $erros[] = "Linha {$linhaNum}: data '{$dataTexto}' inválida (use dd/mm/aaaa).";
                    continue;
                }
            }

            $publicoTexto = strtolower($dados['publico'] ?? 'sim');
            $isPrivate = !in_array($publicoTexto, ['sim', 's', 'yes', '1', 'publico', 'público'], true);

            Informativo::create([
                'title' => $title,
                'content' => $content,
                'sector_id' => $sectorId,
                'is_private' => $isPrivate,
                'published_at' => $publishedAt,
            ]);

            $sucesso++;
        }

        return redirect()->route('informativos.lote.form')
            ->with('status', "{$sucesso} informativo(s) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }

    /**
     * Lê um CSV usando fgetcsv (em vez de dividir o arquivo por linha antes
     * de interpretar), para respeitar corretamente campos entre aspas que
     * contenham quebras de linha — comum no campo "content".
     */
    private function lerLinhasDoCsv(string $caminho): array
    {
        $conteudo = file_get_contents($caminho);
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $conteudo);
        rewind($handle);

        $linhas = [];
        while (($linha = fgetcsv($handle)) !== false) {
            if (count(array_filter($linha, fn ($v) => trim((string) $v) !== '')) > 0) {
                $linhas[] = $linha;
            }
        }
        fclose($handle);

        return $linhas;
    }

    public function reenviarForm(Informativo $informativo)
    {
        $sectors = Sector::orderBy('sigla')->get();
        $sectorId = request()->filled('sector_id') ? request('sector_id') : $informativo->sector_id;

        $query = User::query();
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }
        $emails = $query->orderBy('name')->pluck('email');

        return view('informativos.reenviar', compact('informativo', 'sectors', 'sectorId', 'emails'));
    }

    public function reenviar(Request $request, Informativo $informativo)
    {
        $request->validate(['emails' => 'required|string']);

        $emails = collect(preg_split('/[\r\n,;]+/', $request->input('emails')))
            ->map(fn ($email) => trim($email))
            ->filter()
            ->unique()
            ->values();

        $invalidos = $emails->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        if ($emails->isEmpty() || $invalidos->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'emails' => $invalidos->isNotEmpty()
                    ? 'E-mail(s) inválido(s): ' . $invalidos->implode(', ')
                    : 'Informe ao menos um e-mail.',
            ]);
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(new NovoInformativoMail($informativo));

            InformativoEnvio::create([
                'informativo_id' => $informativo->id,
                'email' => $email,
                'enviado_em' => now(),
            ]);
        }

        return redirect()->route('informativos.show', $informativo)
            ->with('status', "E-mail reenviado para {$emails->count()} destinatário(s).");
    }

    private function enviarNotificacoes(Informativo $informativo): int
    {
        $destinatarios = $this->destinatarios($informativo);

        foreach ($destinatarios as $usuario) {
            Mail::to($usuario->email)->send(new NovoInformativoMail($informativo));

            InformativoEnvio::create([
                'informativo_id' => $informativo->id,
                'email' => $usuario->email,
                'enviado_em' => now(),
            ]);
        }

        return $destinatarios->count();
    }

    private function destinatarios(Informativo $informativo)
    {
        $query = User::query();

        if ($informativo->sector_id) {
            $query->where('sector_id', $informativo->sector_id);
        }

        return $query->get();
    }
}
