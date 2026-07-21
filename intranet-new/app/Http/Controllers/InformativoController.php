<?php

namespace App\Http\Controllers;

use App\Mail\NovoInformativoMail;
use App\Models\Acesso;
use App\Models\Arquivo;
use App\Models\Informativo;
use App\Models\InformativoEnvio;
use App\Models\Sector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        unset($validated['image']);
        $validated['is_private'] = $request->boolean('is_private');
        $validated['published_at'] = now();

        if ($request->hasFile('image')) {
            $validated['arquivo_id'] = $this->salvarImagem($request->file('image'));
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

        unset($validated['image']);
        $validated['is_private'] = $request->boolean('is_private');

        if ($request->hasFile('image')) {
            $validated['arquivo_id'] = $this->salvarImagem($request->file('image'));
        }

        $informativo->update($validated);

        return redirect()->route('informativos.index')->with('status', 'Informativo atualizado com sucesso.');
    }

    /**
     * Salva a imagem do informativo no MinIO (disco "arquivos"), na pasta
     * "Imagens Informativos" do setor do usuário logado, e registra o
     * arquivo no Repositório. Retorna o id do Arquivo criado.
     */
    private function salvarImagem($file): int
    {
        $sector = auth()->user()->sector;
        abort_unless($sector, 422, 'Você precisa estar vinculado a um setor (lotação) para enviar imagens. Atualize seu perfil.');

        $pasta = $sector->pastaImagensInformativos();
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

        $falhas = 0;
        foreach ($emails as $email) {
            if (!$this->enviarEmailInformativo($informativo, $email)) {
                $falhas++;
            }
        }

        $status = 'E-mail reenviado para ' . ($emails->count() - $falhas) . ' destinatário(s).';
        if ($falhas > 0) {
            $status .= " {$falhas} falha(s) no envio.";
        }

        return redirect()->route('informativos.show', $informativo)->with('status', $status);
    }

    private function enviarNotificacoes(Informativo $informativo): int
    {
        $destinatarios = $this->destinatarios($informativo);

        foreach ($destinatarios as $usuario) {
            $this->enviarEmailInformativo($informativo, $usuario->email);
        }

        return $destinatarios->count();
    }

    /**
     * Envia o e-mail de um informativo para um destinatário, sem deixar uma
     * falha (SMTP fora do ar, e-mail inválido no servidor etc.) abortar o
     * restante do lote. Sempre registra o resultado (sucesso ou falha) em
     * InformativoEnvio, para dar visibilidade no monitoramento de saúde.
     */
    private function enviarEmailInformativo(Informativo $informativo, string $email): bool
    {
        try {
            Mail::to($email)->send(new NovoInformativoMail($informativo));

            InformativoEnvio::create([
                'informativo_id' => $informativo->id,
                'email' => $email,
                'sucesso' => true,
                'enviado_em' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar e-mail de informativo', [
                'informativo_id' => $informativo->id,
                'email' => $email,
                'erro' => $e->getMessage(),
            ]);

            InformativoEnvio::create([
                'informativo_id' => $informativo->id,
                'email' => $email,
                'sucesso' => false,
                'erro' => $e->getMessage(),
                'enviado_em' => now(),
            ]);

            return false;
        }
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
