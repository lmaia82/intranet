<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Repositório de Arquivos</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <nav class="text-sm mb-4">
            <a href="{{ route('repositorio.index') }}" class="text-blue-600">Raiz</a>
            <span class="text-gray-400">|</span>
            <a href="{{ route('repositorio.meus') }}" class="text-blue-600">📂 Meus Arquivos</a>
            @foreach($breadcrumb as $item)
                / <a href="{{ route('repositorio.index', ['pasta' => $item->id]) }}" class="text-blue-600">{{ $item->nome }}</a>
            @endforeach
        </nav>

        @if(auth()->user()->hasPermission('repositorio.criar'))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <form method="POST" action="{{ route('repositorio.pastas.store') }}" class="bg-white shadow rounded p-4 space-y-2">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $pastaAtual?->id }}">
                <label class="block text-sm font-medium">Nova pasta</label>
                <input type="text" name="nome" placeholder="Nome da pasta" required class="block w-full border-gray-300 rounded">
                <select name="sector_id" required class="block w-full border-gray-300 rounded">
                    @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" @selected(old('sector_id', auth()->user()->sector_id) == $sector->id)>{{ $sector->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_private" value="1"> Restrita ao setor</label>
                <p class="text-xs text-gray-500">Não marcado = pasta pública (visível a todos). Marcado = visível só para usuários do setor selecionado.</p>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Criar pasta</button>
            </form>

            <form method="POST" action="{{ route('repositorio.arquivos.store') }}" enctype="multipart/form-data" class="bg-white shadow rounded p-4 space-y-2">
                @csrf
                <input type="hidden" name="pasta_id" value="{{ $pastaAtual?->id }}">
                <label class="block text-sm font-medium">Enviar arquivo</label>
                <input type="file" name="arquivo" required class="block w-full">
                @error('arquivo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <input type="text" name="descricao" placeholder="Descrição (opcional)" class="block w-full border-gray-300 rounded">
                <select name="sector_id" required class="block w-full border-gray-300 rounded">
                    @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" @selected(old('sector_id', auth()->user()->sector_id) == $sector->id)>{{ $sector->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_private" value="1"> Restrito ao setor</label>
                <p class="text-xs text-gray-500">Não marcado = arquivo público (visível a todos). Marcado = visível só para usuários do setor selecionado.</p>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Enviar</button>
            </form>
        </div>
        @endif

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Nome</th>
                        <th class="p-3">Tipo</th>
                        <th class="p-3">Tamanho</th>
                        <th class="p-3">Visibilidade</th>
                        @if(auth()->user()->hasPermission('repositorio.criar'))
                            <th class="p-3"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($subpastas as $subpasta)
                        <tr class="border-t">
                            <td class="p-3"><a href="{{ route('repositorio.index', ['pasta' => $subpasta->id]) }}" class="text-blue-700 font-semibold">📁 {{ $subpasta->nome }}</a></td>
                            <td class="p-3">Pasta</td>
                            <td class="p-3">—</td>
                            <td class="p-3">
                                @if($subpasta->is_private)
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-orange-100 text-orange-800">Restrito ({{ $subpasta->sector?->name }})</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Público</span>
                                @endif
                            </td>
                            @if(auth()->user()->hasPermission('repositorio.criar'))
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('repositorio.pastas.editar', $subpasta) }}" class="text-blue-600">Editar</a>
                                    <form action="{{ route('repositorio.pastas.destroy', $subpasta) }}" method="POST" class="inline" onsubmit="return confirm('Remover esta pasta e todo o conteúdo dentro dela?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    @foreach($arquivos as $arquivo)
                        <tr class="border-t">
                            <td class="p-3">
                                <a href="{{ route('repositorio.download', $arquivo) }}" class="text-blue-700">📄 {{ $arquivo->nome_original }}</a>
                                @if(in_array($arquivo->extensao, ['doc','docx','odt','xls','xlsx','ods','ppt','pptx','odp','pdf']))
                                    <a href="{{ route('onlyoffice.editor', $arquivo) }}" class="text-green-700 text-sm ml-2">(abrir no editor)</a>
                                @endif
                            </td>
                            <td class="p-3 uppercase">{{ $arquivo->extensao }}</td>
                            <td class="p-3">{{ $arquivo->tamanhoFormatado() }}</td>
                            <td class="p-3">
                                @if($arquivo->is_private)
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-orange-100 text-orange-800">Restrito ({{ $arquivo->sector?->name }})</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Público</span>
                                @endif
                            </td>
                            @if(auth()->user()->hasPermission('repositorio.criar'))
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('repositorio.arquivos.editar', $arquivo) }}" class="text-blue-600">Editar</a>
                                    <form action="{{ route('repositorio.arquivos.destroy', $arquivo) }}" method="POST" class="inline" onsubmit="return confirm('Remover este arquivo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    @if($subpastas->isEmpty() && $arquivos->isEmpty())
                        <tr><td colspan="{{ auth()->user()->hasPermission('repositorio.criar') ? 5 : 4 }}" class="p-3 text-gray-500">Pasta vazia.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
