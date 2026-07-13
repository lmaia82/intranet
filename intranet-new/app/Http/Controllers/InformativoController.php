<?php

namespace App\Http\Controllers;

use App\Mail\NovoInformativoMail;
use App\Models\Informativo;
use App\Models\InformativoEnvio;
use App\Models\Sector;
use App\Models\User;
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
        $sectors = Sector::orderBy('name')->get();

        return view('informativos.index', compact('informativos', 'sectors'));
    }

    public function show(Informativo $informativo)
    {
        $informativo->load('sector', 'envios');

        return view('informativos.show', compact('informativo'));
    }

    public function create()
    {
        $sectors = Sector::orderBy('name')->get();
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
        $sectors = Sector::orderBy('name')->get();
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

    public function reenviarForm(Informativo $informativo)
    {
        $sectors = Sector::orderBy('name')->get();
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
