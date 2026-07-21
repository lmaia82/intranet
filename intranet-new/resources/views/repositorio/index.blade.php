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
                        <option value="{{ $sector->id }}" @selected(old('sector_id', auth()->user()->sector_id) == $sector->id)>{{ $sector->sigla }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_private" value="1"> Restrita ao setor</label>
                <p class="text-xs text-gray-500">Não marcado = pasta pública (visível a todos). Marcado = visível só para usuários do setor selecionado.</p>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Criar pasta</button>
            </form>

            <form method="POST" action="{{ route('repositorio.arquivos.store') }}" enctype="multipart/form-data" class="bg-white shadow rounded p-4 space-y-2">
                @csrf
                <label class="block text-sm font-medium">Enviar arquivo</label>
                <input type="file" name="arquivo" required class="block w-full">
                @error('arquivo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <label class="block text-sm font-medium">Pasta de destino</label>
                @if($pastasParaSelecao->isEmpty())
                    <p class="text-sm text-red-600">Nenhuma pasta disponível. Crie uma pasta antes de enviar arquivos.</p>
                @else
                    <select name="pasta_id" required class="block w-full border-gray-300 rounded">
                        <option value="" disabled @selected(!old('pasta_id', $pastaAtual?->id))>Selecione uma pasta</option>
                        @foreach($pastasParaSelecao as $opcao)
                            <option value="{{ $opcao['id'] }}" @selected(old('pasta_id', $pastaAtual?->id) == $opcao['id'])>{{ $opcao['caminho'] }}</option>
                        @endforeach
                    </select>
                    @error('pasta_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                @endif
                <input type="text" name="descricao" placeholder="Descrição (opcional)" class="block w-full border-gray-300 rounded">
                <label class="block text-sm font-medium">Data do documento</label>
                <input type="date" name="data" value="{{ old('data', now()->format('Y-m-d')) }}" class="block w-full border-gray-300 rounded">
                <select name="sector_id" required class="block w-full border-gray-300 rounded">
                    @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" @selected(old('sector_id', auth()->user()->sector_id) == $sector->id)>{{ $sector->sigla }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_private" value="1"> Restrito ao setor</label>
                <p class="text-xs text-gray-500">Não marcado = arquivo público (visível a todos). Marcado = visível só para usuários do setor selecionado.</p>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" @disabled($pastasParaSelecao->isEmpty())>Enviar</button>
            </form>
        </div>
        <p class="text-sm mb-6"><a href="{{ route('repositorio.arquivos.lote.form') }}" class="text-blue-600">📦 Cadastro em lote de arquivos</a></p>
        @endif

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Nome</th>
                        <th class="p-3">Tipo</th>
                        <th class="p-3">Tamanho</th>
                        <th class="p-3">Data</th>
                        <th class="p-3">OCR</th>
                        <th class="p-3">Visibilidade</th>
                        <th class="p-3">Criado por</th>
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
                            <td class="p-3">—</td>
                            <td class="p-3">—</td>
                            <td class="p-3">
                                @if($subpasta->is_private)
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-orange-100 text-orange-800">Restrito ({{ $subpasta->sector?->sigla }})</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Público</span>
                                @endif
                            </td>
                            <td class="p-3">—</td>
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
                                    <a href="{{ route('onlyoffice.editor', $arquivo) }}" target="_blank" rel="noopener" class="text-green-700 text-sm ml-2">(abrir no editor)</a>
                                @endif
                            </td>
                            <td class="p-3 uppercase">{{ $arquivo->extensao }}</td>
                            <td class="p-3">{{ $arquivo->tamanhoFormatado() }}</td>
                            <td class="p-3">{{ optional($arquivo->data)->format('d/m/Y') }}</td>
                            <td class="p-3">
                                @if($arquivo->extensao === 'pdf')
                                    <div
                                        x-data="ocrStatus(@js($arquivo->ocr_status), @js($arquivo->ocr_erro), @js(route('repositorio.arquivos.ocr-status', $arquivo)))"
                                        @if($arquivo->ocr_status === 'pendente') x-init="iniciarPolling()" @endif
                                    >
                                        <span x-show="status === 'concluido'" x-cloak class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800" title="Arquivo já pesquisável e com texto selecionável">✅ Concluído</span>
                                        <span x-show="status === 'pendente'" x-cloak class="inline-block px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800" title="Processando em segundo plano — atualiza sozinho">⏳ Processando</span>
                                        <span x-show="status === 'falhou'" x-cloak class="inline-block px-2 py-0.5 text-xs rounded bg-red-100 text-red-800" x-bind:title="erro || 'Não foi possível processar o OCR'">⚠️ Falhou</span>
                                        <span x-show="!status" x-cloak class="text-xs text-gray-400">—</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($arquivo->is_private)
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-orange-100 text-orange-800">Restrito ({{ $arquivo->sector?->sigla }})</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Público</span>
                                @endif
                            </td>
                            <td class="p-3 text-sm text-gray-600">{{ $arquivo->criadoPor?->email ?? '—' }}</td>
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
                        <tr><td colspan="{{ auth()->user()->hasPermission('repositorio.criar') ? 7 : 6 }}" class="p-3 text-gray-500">Pasta vazia.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
