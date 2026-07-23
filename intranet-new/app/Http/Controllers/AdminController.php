<?php

namespace App\Http\Controllers;

use App\Models\Acesso;
use App\Models\Arquivo;
use App\Models\Configuracao;
use App\Models\Destaque;
use App\Models\Evento;
use App\Models\Group;
use App\Models\Informativo;
use App\Models\InformativoEnvio;
use App\Models\Permission;
use App\Models\Sector;
use App\Models\Telefone;
use App\Models\User;
use App\Services\ActiveDirectoryAuthenticator;
use App\Services\HealthCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'usuarios' => User::where('is_active', true)->count(),
            'setores' => Sector::count(),
            'grupos' => Group::count(),
            'ramais' => Telefone::count(),
            'informativos' => Informativo::count(),
            'eventos' => Evento::count(),
            'destaques' => Destaque::count(),
            'arquivos' => Arquivo::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    public function setores()
    {
        $setores = Sector::with('parent')->orderBy('sigla')->get();
        $coordenacoes = $setores->whereNull('parent_id');
        return view('admin.setores', compact('setores', 'coordenacoes'));
    }

    public function engajamento()
    {
        $nomesModulos = [
            'dashboard' => 'Tela Inicial',
            'ramais' => 'Ramais',
            'informativos' => 'Informativos',
            'eventos' => 'Agenda',
            'repositorio' => 'Repositório',
            'tutoriais' => 'Tutoriais',
            'destaques' => 'Destaques',
            'busca' => 'Busca',
        ];

        $hoje = now()->startOfDay();
        $inicio7d = now()->subDays(6)->startOfDay();
        $inicio30d = now()->subDays(29)->startOfDay();
        $inicioGrafico = now()->subDays(13)->startOfDay();

        $usuariosAtivosHoje = Acesso::where('created_at', '>=', $hoje)->distinct('user_id')->count('user_id');
        $usuariosAtivos7d = Acesso::where('created_at', '>=', $inicio7d)->distinct('user_id')->count('user_id');
        $usuariosAtivos30d = Acesso::where('created_at', '>=', $inicio30d)->distinct('user_id')->count('user_id');

        $acessosPorModulo = Acesso::where('created_at', '>=', $inicio30d)
            ->selectRaw('modulo, count(*) as total')
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        $registrosPorDia = Acesso::where('created_at', '>=', $inicioGrafico)
            ->selectRaw('DATE(created_at) as dia, count(distinct user_id) as usuarios, count(*) as acessos')
            ->groupBy('dia')
            ->get()
            ->keyBy('dia');

        $dias = collect();
        for ($dia = $inicioGrafico->copy(); $dia->lte($hoje); $dia->addDay()) {
            $registro = $registrosPorDia->get($dia->toDateString());
            $dias->push([
                'data' => $dia->copy(),
                'usuarios' => $registro->usuarios ?? 0,
                'acessos' => $registro->acessos ?? 0,
            ]);
        }

        $maxUsuariosDia = max($dias->max('usuarios'), 1);
        $maxAcessosModulo = max($acessosPorModulo->max('total'), 1);

        return view('admin.engajamento', compact(
            'usuariosAtivosHoje', 'usuariosAtivos7d', 'usuariosAtivos30d',
            'acessosPorModulo', 'dias', 'maxUsuariosDia', 'maxAcessosModulo', 'nomesModulos'
        ));
    }

    public function conteudo()
    {
        $inicio30d = now()->subDays(29)->startOfDay();

        $informativosMaisLidos = Acesso::where('modulo', 'informativos')
            ->where('referencia_tipo', 'informativo')
            ->where('created_at', '>=', $inicio30d)
            ->selectRaw('referencia_id, count(*) as total')
            ->groupBy('referencia_id')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->map(fn ($registro) => (object) [
                'total' => $registro->total,
                'item' => Informativo::find($registro->referencia_id),
            ])
            ->filter(fn ($registro) => $registro->item !== null)
            ->values();

        $arquivosMaisBaixados = Acesso::where('modulo', 'repositorio')
            ->where('referencia_tipo', 'arquivo')
            ->where('created_at', '>=', $inicio30d)
            ->selectRaw('referencia_id, count(*) as total')
            ->groupBy('referencia_id')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->map(fn ($registro) => (object) [
                'total' => $registro->total,
                'item' => Arquivo::find($registro->referencia_id),
            ])
            ->filter(fn ($registro) => $registro->item !== null)
            ->values();

        $termosMaisBuscados = Acesso::where('modulo', 'busca')
            ->whereNotNull('termo')
            ->where('created_at', '>=', $inicio30d)
            ->selectRaw('LOWER(termo) as termo, count(*) as total')
            ->groupBy('termo')
            ->orderByDesc('total')
            ->take(15)
            ->get();

        $buscasSemResultado = Acesso::where('modulo', 'busca')
            ->whereNotNull('termo')
            ->where('resultados', 0)
            ->where('created_at', '>=', $inicio30d)
            ->selectRaw('LOWER(termo) as termo, count(*) as total')
            ->groupBy('termo')
            ->orderByDesc('total')
            ->take(15)
            ->get();

        $maxInformativo = max($informativosMaisLidos->max('total'), 1);
        $maxArquivo = max($arquivosMaisBaixados->max('total'), 1);
        $maxTermo = max($termosMaisBuscados->max('total'), 1);

        return view('admin.conteudo', compact(
            'informativosMaisLidos', 'arquivosMaisBaixados', 'termosMaisBuscados', 'buscasSemResultado',
            'maxInformativo', 'maxArquivo', 'maxTermo'
        ));
    }

    public function saude()
    {
        $inicio30d = now()->subDays(29)->startOfDay();

        $ocrPorStatus = Arquivo::where('extensao', 'pdf')
            ->whereNotNull('ocr_status')
            ->selectRaw('ocr_status, count(*) as total')
            ->groupBy('ocr_status')
            ->pluck('total', 'ocr_status');

        $arquivosComFalhaOcr = Arquivo::where('ocr_status', 'falhou')
            ->latest('updated_at')
            ->take(10)
            ->get(['id', 'nome_original', 'ocr_erro', 'updated_at']);

        $servicos = app(HealthCheckService::class)->verificarTodos();

        $enviosEmail = InformativoEnvio::where('created_at', '>=', $inicio30d)
            ->selectRaw('sucesso, count(*) as total')
            ->groupBy('sucesso')
            ->get()
            ->mapWithKeys(fn ($registro) => [$registro->sucesso ? 'sucesso' : 'falha' => $registro->total]);

        $emailsComFalha = InformativoEnvio::where('sucesso', false)
            ->where('created_at', '>=', $inicio30d)
            ->with('informativo')
            ->latest('created_at')
            ->take(10)
            ->get();

        $setoresProximosDaCota = Sector::whereNotNull('quota_bytes')
            ->get()
            ->filter(fn ($sector) => $sector->percentualUso() !== null && $sector->percentualUso() >= 80)
            ->sortByDesc(fn ($sector) => $sector->percentualUso())
            ->values();

        return view('admin.saude', compact(
            'servicos', 'ocrPorStatus', 'arquivosComFalhaOcr',
            'enviosEmail', 'emailsComFalha', 'setoresProximosDaCota'
        ));
    }

    public function configuracoes()
    {
        $configuracao = Configuracao::atual();
        return view('admin.configuracoes', compact('configuracao'));
    }

    public function togglePreviaLogin()
    {
        $configuracao = Configuracao::atual();
        $configuracao->update(['previa_login_ativa' => !$configuracao->previa_login_ativa]);

        return redirect()->route('admin.configuracoes')->with('status', 'Configuração atualizada.');
    }

    public function toggleTutoriais()
    {
        $configuracao = Configuracao::atual();
        $configuracao->update(['tutoriais_ativo' => !$configuracao->tutoriais_ativo]);

        return redirect()->route('admin.configuracoes')->with('status', 'Configuração atualizada.');
    }

    public function atualizarTempoInatividade(Request $request)
    {
        $validated = $request->validate([
            'tempo_inatividade_minutos' => 'required|integer|min:1|max:43200',
        ]);

        Configuracao::atual()->update($validated);

        return redirect()->route('admin.configuracoes')->with('status', 'Tempo de inatividade atualizado.');
    }

    public function storeSetor(Request $request)
    {
        $validated = $request->validate([
            'sigla' => 'required|string|max:100|unique:sectors,sigla',
            'nome' => 'nullable|string|max:150',
            'quota_mb' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:sectors,id',
        ]);
        Sector::create([
            'sigla' => $validated['sigla'],
            'nome' => $validated['nome'] ?? null,
            'quota_bytes' => $this->mbParaBytes($validated['quota_mb'] ?? null),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);
        return redirect()->route('admin.setores')->with('status', 'Setor criado com sucesso.');
    }

    public function updateSetor(Request $request, Sector $setor)
    {
        $validated = $request->validate([
            'sigla' => 'required|string|max:100|unique:sectors,sigla,' . $setor->id,
            'nome' => 'nullable|string|max:150',
            'quota_mb' => 'nullable|numeric|min:0',
            'parent_id' => ['nullable', 'exists:sectors,id', Rule::notIn([$setor->id])],
        ]);
        $setor->update([
            'sigla' => $validated['sigla'],
            'nome' => $validated['nome'] ?? null,
            'quota_bytes' => $this->mbParaBytes($validated['quota_mb'] ?? null),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);
        return redirect()->route('admin.setores')->with('status', 'Setor atualizado com sucesso.');
    }

    public function destroySetor(Sector $setor)
    {
        $setor->delete();
        return redirect()->route('admin.setores')->with('status', 'Setor removido.');
    }

    private function mbParaBytes($mb): ?int
    {
        return $mb !== null && $mb !== '' ? (int) round($mb * 1048576) : null;
    }

    public function armazenamento()
    {
        $setores = Sector::orderBy('sigla')->get();
        return view('admin.armazenamento', compact('setores'));
    }

    public function grupos()
    {
        $grupos = Group::with('permissions')->orderBy('name')->get();
        return view('admin.grupos', compact('grupos'));
    }

    public function criarGrupoForm()
    {
        $permissoes = $this->permissoesPorTela();
        return view('admin.criar-grupo', compact('permissoes'));
    }

    public function storeGrupo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:groups,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $grupo = Group::create(['name' => $validated['name']]);
        $grupo->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.grupos')->with('status', 'Grupo criado com sucesso.');
    }

    public function editarGrupoForm(Group $grupo)
    {
        $grupo->load('permissions');
        $permissoes = $this->permissoesPorTela();
        return view('admin.editar-grupo', compact('grupo', 'permissoes'));
    }

    public function updateGrupo(Request $request, Group $grupo)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:groups,name,' . $grupo->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $grupo->update(['name' => $validated['name']]);
        $grupo->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.grupos')->with('status', 'Grupo atualizado com sucesso.');
    }

    public function destroyGrupo(Group $grupo)
    {
        $grupo->delete();
        return redirect()->route('admin.grupos')->with('status', 'Grupo removido.');
    }

    private function permissoesPorTela()
    {
        return Permission::orderBy('key')->get()->groupBy(function ($permission) {
            return explode('.', $permission->key)[0];
        });
    }

    public function usuarios(Request $request)
    {
        $totalGeral = User::count();

        $usuarios = User::with(['sector', 'group'])->orderBy('name')->get();

        if ($nome = trim((string) $request->input('nome'))) {
            $usuarios = $usuarios->filter(fn ($u) => str_contains(Str::lower($u->name), Str::lower($nome)));
        }

        if ($email = trim((string) $request->input('email'))) {
            $usuarios = $usuarios->filter(fn ($u) => str_contains(Str::lower($u->email), Str::lower($email)));
        }

        if ($request->filled('sector_id')) {
            $usuarios = $usuarios->where('sector_id', $request->input('sector_id'));
        }

        if ($request->filled('ad_setor')) {
            $usuarios = $usuarios->where('ad_setor', $request->input('ad_setor'));
        }

        if ($request->filled('confere')) {
            $confere = $request->input('confere');
            $usuarios = $usuarios->filter(function ($u) use ($confere) {
                $bate = $u->setorBateComAd();

                return match ($confere) {
                    'sim' => $bate === true,
                    'nao' => $bate === false,
                    'sem_ad' => is_null($bate),
                    default => true,
                };
            });
        }

        if ($request->filled('group_id')) {
            $usuarios = $usuarios->where('group_id', $request->input('group_id'));
        }

        if ($request->filled('is_admin')) {
            $usuarios = $usuarios->where('is_admin', (bool) (int) $request->input('is_admin'));
        }

        if ($request->filled('is_active')) {
            $usuarios = $usuarios->where('is_active', (bool) (int) $request->input('is_active'));
        }

        if ($request->filled('dominio_email')) {
            $dominioEmail = $request->input('dominio_email');
            $usuarios = $usuarios->filter(function ($u) use ($dominioEmail) {
                $interno = str_ends_with(Str::lower($u->email), '@cetem.gov.br');

                return $dominioEmail === 'cetem' ? $interno : ! $interno;
            });
        }

        $setores = Sector::orderBy('sigla')->get();
        $grupos = Group::orderBy('name')->get();
        $adSetores = User::whereNotNull('ad_setor')->distinct()->orderBy('ad_setor')->pluck('ad_setor');

        return view('admin.usuarios', compact('usuarios', 'setores', 'grupos', 'adSetores', 'totalGeral'));
    }

    public function importarUsuariosDoAd(Request $request)
    {
        $validated = $request->validate(['password' => 'required|string']);

        $importados = app(ActiveDirectoryAuthenticator::class)
            ->importarUsuariosAtivos($request->user()->email, $validated['password']);

        if (is_null($importados)) {
            return redirect()->route('admin.usuarios')->with('status', 'Senha do AD incorreta — importação cancelada.');
        }

        return redirect()->route('admin.usuarios')->with('status', "Importação concluída: {$importados} usuário(s) novo(s) trazido(s) do AD.");
    }

    public function criarUsuarioForm()
    {
        $setores = Sector::orderBy('sigla')->get();
        $grupos = Group::orderBy('name')->get();
        return view('admin.criar-usuario', compact('setores', 'grupos'));
    }

    public function storeUsuario(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'sector_id' => 'nullable|exists:sectors,id',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->boolean('is_admin'),
            'sector_id' => $validated['sector_id'] ?? null,
            'group_id' => $validated['group_id'] ?? null,
        ]);

        return redirect()->route('admin.usuarios')->with('status', 'Usuário criado com sucesso.');
    }

    public function editarUsuarioForm(User $usuario)
    {
        $setores = Sector::orderBy('sigla')->get();
        $grupos = Group::orderBy('name')->get();
        return view('admin.editar-usuario', compact('usuario', 'setores', 'grupos'));
    }

    public function updateUsuario(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => 'nullable|confirmed|min:8',
        ]);

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->save();

        return redirect()->route('admin.usuarios')->with('status', 'Usuário atualizado com sucesso.');
    }

    public function updateUsuarioSetor(Request $request, User $usuario)
    {
        $validated = $request->validate(['sector_id' => 'nullable|exists:sectors,id']);
        $usuario->update(['sector_id' => $validated['sector_id'] ?? null]);
        return redirect()->route('admin.usuarios')->with('status', 'Setor do usuário atualizado.');
    }

    public function trazerSetorDoAd(User $usuario)
    {
        abort_if(is_null($usuario->ad_setor), 404, 'Usuário sem setor importado do AD.');

        if ($usuario->sector_id) {
            return redirect()->route('admin.usuarios')->with('status', 'O usuário já possui um setor definido na intranet — remova-o antes de trazer do AD.');
        }

        $setor = Sector::where('sigla', $usuario->ad_setor)->first();

        if (!$setor) {
            return redirect()->route('admin.usuarios')->with('status', "Nenhum setor da intranet corresponde a \"{$usuario->ad_setor}\" (AD). Cadastre o setor ou ajuste manualmente.");
        }

        $usuario->update(['sector_id' => $setor->id]);

        return redirect()->route('admin.usuarios')->with('status', 'Setor trazido do AD com sucesso.');
    }

    public function updateUsuarioGrupo(Request $request, User $usuario)
    {
        $validated = $request->validate(['group_id' => 'nullable|exists:groups,id']);
        $usuario->update(['group_id' => $validated['group_id'] ?? null]);
        return redirect()->route('admin.usuarios')->with('status', 'Grupo do usuário atualizado.');
    }

    public function toggleAdmin(User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'Você não pode alterar sua própria permissão.');
        $usuario->update(['is_admin' => !$usuario->is_admin]);
        return redirect()->route('admin.usuarios')->with('status', 'Permissão atualizada.');
    }

    public function toggleAtivo(User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'Você não pode desativar a si mesmo.');
        $usuario->update(['is_active' => !$usuario->is_active]);
        return redirect()->route('admin.usuarios')->with('status', $usuario->is_active ? 'Usuário ativado.' : 'Usuário desativado.');
    }

    public function destroyUsuario(User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'Você não pode remover a si mesmo.');
        $usuario->delete();
        return redirect()->route('admin.usuarios')->with('status', 'Usuário removido.');
    }

    public function destroyUsuariosLote(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = collect($validated['ids'])->reject(fn ($id) => (int) $id === auth()->id());

        User::whereIn('id', $ids)->delete();

        return redirect()->route('admin.usuarios', $this->filtrosAtuais($request))
            ->with('status', $ids->count() . ' usuário(s) removido(s).');
    }

    public function desativarUsuariosLote(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = collect($validated['ids'])->reject(fn ($id) => (int) $id === auth()->id());

        User::whereIn('id', $ids)->update(['is_active' => false]);

        return redirect()->route('admin.usuarios', $this->filtrosAtuais($request))
            ->with('status', $ids->count() . ' usuário(s) desativado(s).');
    }

    public function ativarUsuariosLote(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        User::whereIn('id', $validated['ids'])->update(['is_active' => true]);

        return redirect()->route('admin.usuarios', $this->filtrosAtuais($request))
            ->with('status', count($validated['ids']) . ' usuário(s) ativado(s).');
    }

    public function atualizarSetorUsuariosLote(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'novo_sector_id' => 'nullable|exists:sectors,id',
        ]);

        User::whereIn('id', $validated['ids'])->update(['sector_id' => $validated['novo_sector_id'] ?? null]);

        return redirect()->route('admin.usuarios', $this->filtrosAtuais($request))
            ->with('status', count($validated['ids']) . ' usuário(s) com setor atualizado.');
    }

    private function filtrosAtuais(Request $request): array
    {
        return $request->only(['nome', 'email', 'sector_id', 'ad_setor', 'confere', 'group_id', 'is_admin', 'is_active', 'dominio_email']);
    }

    public function usuariosLoteForm()
    {
        return view('admin.usuarios-lote');
    }

    public function usuariosLoteTemplate()
    {
        $csv = "nome,email,senha,setor,grupo,admin\n";
        $csv .= "Fulano de Tal,fulano@cetem.gov.br,SenhaProvisoria123,TI,Colaboradores,nao\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_usuarios.csv"',
        ]);
    }

    public function usuariosLoteImport(Request $request)
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
            $email = trim($dados['email'] ?? '');
            $senha = trim($dados['senha'] ?? '');
            $setorNome = trim($dados['setor'] ?? '');
            $grupoNome = trim($dados['grupo'] ?? '');

            if ($nome === '' || $email === '' || $senha === '') {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios em branco.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = "Linha {$linhaNum}: e-mail '{$email}' inválido.";
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $erros[] = "Linha {$linhaNum}: já existe um usuário com o e-mail '{$email}'.";
                continue;
            }

            $sectorId = null;
            if ($setorNome !== '') {
                $sector = Sector::whereRaw('LOWER(sigla) = ?', [mb_strtolower($setorNome)])->first();
                if (!$sector) {
                    $erros[] = "Linha {$linhaNum}: setor '{$setorNome}' não encontrado.";
                    continue;
                }
                $sectorId = $sector->id;
            }

            $groupId = null;
            if ($grupoNome !== '') {
                $group = Group::whereRaw('LOWER(name) = ?', [mb_strtolower($grupoNome)])->first();
                if (!$group) {
                    $erros[] = "Linha {$linhaNum}: grupo '{$grupoNome}' não encontrado.";
                    continue;
                }
                $groupId = $group->id;
            }

            $admin = in_array(mb_strtolower(trim($dados['admin'] ?? '')), ['sim', 's', 'yes', '1', 'true']);

            User::create([
                'name' => $nome,
                'email' => $email,
                'password' => Hash::make($senha),
                'sector_id' => $sectorId,
                'group_id' => $groupId,
                'is_admin' => $admin,
            ]);

            $sucesso++;
        }

        return redirect()->route('admin.usuarios.lote.form')
            ->with('status', "{$sucesso} usuário(s) importado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }

    public function usuariosGrupoLoteForm()
    {
        return view('admin.usuarios-grupo-lote');
    }

    public function usuariosGrupoLoteTemplate()
    {
        $csv = "email,grupo\n";
        $csv .= "fulano@cetem.gov.br,Colaboradores\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_usuarios_grupo.csv"',
        ]);
    }

    public function usuariosGrupoLoteImport(Request $request)
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

            $email = trim($dados['email'] ?? '');
            $grupoNome = trim($dados['grupo'] ?? '');

            if ($email === '' || $grupoNome === '') {
                $erros[] = "Linha {$linhaNum}: campos obrigatórios em branco.";
                continue;
            }

            $usuario = User::where('email', $email)->first();
            if (!$usuario) {
                $erros[] = "Linha {$linhaNum}: usuário com e-mail '{$email}' não encontrado.";
                continue;
            }

            $group = Group::whereRaw('LOWER(name) = ?', [mb_strtolower($grupoNome)])->first();
            if (!$group) {
                $erros[] = "Linha {$linhaNum}: grupo '{$grupoNome}' não encontrado.";
                continue;
            }

            $usuario->update(['group_id' => $group->id]);
            $sucesso++;
        }

        return redirect()->route('admin.usuarios.grupo-lote.form')
            ->with('status', "{$sucesso} usuário(s) atualizado(s) com sucesso.")
            ->with('erros_lote', $erros);
    }
}
