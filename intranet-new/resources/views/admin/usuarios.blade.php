<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuários</h2>
    </x-slot>
    <div class="py-6 w-full mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="flex justify-end mb-4 gap-3">
            <details class="relative">
                <summary class="px-4 py-2 bg-gray-200 rounded cursor-pointer select-none list-none">Importar do AD</summary>
                <form action="{{ route('admin.usuarios.importar-do-ad') }}" method="POST"
                      class="absolute right-0 mt-2 z-10 w-80 bg-white shadow-lg rounded p-4 border">
                    @csrf
                    <p class="text-sm text-gray-600 mb-2">
                        Importa todos os usuários ativos do AD que ainda não existem na intranet, com o setor
                        importado e no grupo Leitores. Confirme com sua própria senha do AD.
                    </p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sua senha do AD</label>
                    <input type="password" name="password" required class="w-full border-gray-300 rounded text-sm mb-3">
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded">Confirmar importação</button>
                </form>
            </details>
            <a href="{{ route('admin.usuarios.grupo-lote.form') }}" class="px-4 py-2 bg-gray-200 rounded">Associar grupos em lote</a>
            <a href="{{ route('admin.usuarios.lote.form') }}" class="px-4 py-2 bg-gray-200 rounded">Cadastro em lote</a>
            <a href="{{ route('admin.usuarios.criar') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo usuário</a>
        </div>

        <form action="{{ route('admin.usuarios') }}" method="GET" class="bg-white shadow rounded p-4 mb-4">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nome</label>
                    <input type="text" name="nome" value="{{ request('nome') }}" class="w-full border-gray-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">E-mail</label>
                    <input type="text" name="email" value="{{ request('email') }}" class="w-full border-gray-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Setor (Intranet)</label>
                    <select name="sector_id" class="w-full border-gray-300 rounded text-sm">
                        <option value="">(todos)</option>
                        <option value="none" @selected(request('sector_id') === 'none')>Sem setor</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" @selected(request('sector_id') == $setor->id)>{{ $setor->sigla }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Setor (AD)</label>
                    <select name="ad_setor" class="w-full border-gray-300 rounded text-sm">
                        <option value="">(todos)</option>
                        @foreach($adSetores as $adSetor)
                            <option value="{{ $adSetor }}" @selected(request('ad_setor') === $adSetor)>{{ $adSetor }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Confere?</label>
                    <select name="confere" class="w-full border-gray-300 rounded text-sm">
                        <option value="">(todos)</option>
                        <option value="sim" @selected(request('confere') === 'sim')>Sim</option>
                        <option value="nao" @selected(request('confere') === 'nao')>Não</option>
                        <option value="sem_ad" @selected(request('confere') === 'sem_ad')>Sem conta no AD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Grupo</label>
                    <select name="group_id" class="w-full border-gray-300 rounded text-sm">
                        <option value="">(todos)</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" @selected(request('group_id') == $grupo->id)>{{ $grupo->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admin</label>
                    <select name="is_admin" class="w-full border-gray-300 rounded text-sm">
                        <option value="">(todos)</option>
                        <option value="1" @selected(request('is_admin') === '1')>Sim</option>
                        <option value="0" @selected(request('is_admin') === '0')>Não</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="is_active" class="w-full border-gray-300 rounded text-sm">
                        <option value="" @selected(request()->has('is_active') && request('is_active') === '')>(todos)</option>
                        <option value="1" @selected(!request()->has('is_active') || request('is_active') === '1')>Ativos</option>
                        <option value="0" @selected(request('is_active') === '0')>Inativos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Domínio do e-mail</label>
                    <select name="dominio_email" class="w-full border-gray-300 rounded text-sm">
                        <option value="">(todos)</option>
                        <option value="cetem" @selected(request('dominio_email') === 'cetem')>@cetem.gov.br</option>
                        <option value="externo" @selected(request('dominio_email') === 'externo')>Externo</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Filtrar</button>
                <a href="{{ route('admin.usuarios') }}" class="px-4 py-2 bg-gray-200 rounded text-sm">Limpar filtros</a>
            </div>
        </form>

        <form id="acao-lote-form" action="{{ route('admin.usuarios.destroy-lote') }}" method="POST">
            @csrf
            @foreach(request()->only(['nome', 'email', 'sector_id', 'ad_setor', 'confere', 'group_id', 'is_admin', 'is_active', 'dominio_email']) as $chave => $valor)
                <input type="hidden" name="{{ $chave }}" value="{{ $valor }}">
            @endforeach
        </form>

        <div class="flex justify-between items-center mb-2 gap-3">
            <p class="text-sm text-gray-600">
                Exibindo <strong>{{ $usuarios->count() }}</strong> de <strong>{{ $totalGeral }}</strong> usuário(s)
            </p>
            <div class="flex gap-3 items-center">
            <select name="novo_sector_id" form="acao-lote-form" class="border-gray-300 rounded text-sm">
                <option value="">Setor (Intranet)...</option>
                @foreach($setores as $setor)
                    <option value="{{ $setor->id }}">{{ $setor->sigla }}</option>
                @endforeach
            </select>
            <button type="submit" form="acao-lote-form" formaction="{{ route('admin.usuarios.setor-lote') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded text-sm"
                    onclick="return confirm('Atualizar o setor dos usuários selecionados?')">
                Atualizar setor dos selecionados
            </button>
            <button type="submit" form="acao-lote-form" formaction="{{ route('admin.usuarios.desativar-lote') }}"
                    class="px-4 py-2 bg-amber-600 text-white rounded text-sm"
                    onclick="return confirm('Desativar os usuários selecionados?')">
                Desativar selecionados
            </button>
            <button type="submit" form="acao-lote-form" formaction="{{ route('admin.usuarios.ativar-lote') }}"
                    class="px-4 py-2 bg-green-600 text-white rounded text-sm"
                    onclick="return confirm('Ativar os usuários selecionados?')">
                Ativar selecionados
            </button>
            <button type="submit" form="acao-lote-form" formaction="{{ route('admin.usuarios.destroy-lote') }}"
                    class="px-4 py-2 bg-red-600 text-white rounded text-sm"
                    onclick="return confirm('Remover os usuários selecionados? Esta ação não pode ser desfeita.')">
                Excluir selecionados
            </button>
            </div>
        </div>

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">
                            <input type="checkbox" onclick="document.querySelectorAll('.selecionar-usuario').forEach(cb => cb.checked = this.checked)">
                        </th>
                        <th class="p-3">Nome</th>
                        <th class="p-3">E-mail</th>
                        <th class="p-3">Setor (Intranet)</th>
                        <th class="p-3">Setor (AD)</th>
                        <th class="p-3" title="Compara o setor da intranet com o setor trazido do AD">Confere?</th>
                        <th class="p-3">Grupo</th>
                        <th class="p-3">Admin</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Criado em</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                        <tr class="border-t">
                            <td class="p-3">
                                @if($usuario->id !== auth()->id())
                                    <input type="checkbox" class="selecionar-usuario" name="ids[]" value="{{ $usuario->id }}" form="acao-lote-form">
                                @endif
                            </td>
                            <td class="p-3">{{ $usuario->name }}</td>
                            <td class="p-3">{{ $usuario->email }}</td>
                            <td class="p-3">
                                <form action="{{ route('admin.usuarios.setor', $usuario) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="sector_id" class="border-gray-300 rounded text-sm" onchange="this.form.submit()">
                                        <option value="">(nenhum)</option>
                                        @foreach($setores as $setor)
                                            <option value="{{ $setor->id }}" @selected($usuario->sector_id == $setor->id)>{{ $setor->sigla }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="p-3">{{ $usuario->ad_setor ?? '—' }}</td>
                            <td class="p-3 text-center">
                                @php($confere = $usuario->setorBateComAd())
                                @if(is_null($confere))
                                    <span class="text-gray-400" title="Usuário sem conta no AD">—</span>
                                @elseif($confere)
                                    <span class="text-green-600" title="Setor da intranet confere com o AD">✅</span>
                                @else
                                    <span class="text-amber-600" title="Setor da intranet diferente do AD">⚠️</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <form action="{{ route('admin.usuarios.grupo', $usuario) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="group_id" class="border-gray-300 rounded text-sm" onchange="this.form.submit()">
                                        <option value="">(nenhum)</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}" @selected($usuario->group_id == $grupo->id)>{{ $grupo->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="p-3">{{ $usuario->is_admin ? 'Sim' : 'Não' }}</td>
                            <td class="p-3">
                                <span class="{{ $usuario->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $usuario->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="p-3">{{ $usuario->created_at->format('d/m/Y') }}</td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.usuarios.editar', $usuario) }}" class="text-blue-600">Editar</a>
                                @if($usuario->id !== auth()->id())
                                    <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 ml-2">{{ $usuario->is_admin ? 'Remover admin' : 'Tornar admin' }}</button>
                                    </form>
                                    <form action="{{ route('admin.usuarios.toggle-ativo', $usuario) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="{{ $usuario->is_active ? 'text-amber-600' : 'text-green-600' }} ml-2">{{ $usuario->is_active ? 'Desativar' : 'Ativar' }}</button>
                                    </form>
                                    <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" class="inline" onsubmit="return confirm('Remover este usuário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm ml-2">(você)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
