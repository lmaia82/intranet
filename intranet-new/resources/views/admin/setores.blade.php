<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Setores</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @error('sigla')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ $message }}</div>
        @enderror
        @error('nome')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ $message }}</div>
        @enderror
        @error('quota_mb')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ $message }}</div>
        @enderror

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.armazenamento') }}" class="text-blue-600 text-sm">Ver dashboard de armazenamento &rarr;</a>
        </div>

        <form method="POST" action="{{ route('admin.setores.store') }}" class="bg-white shadow rounded p-4 mb-6 flex gap-2">
            @csrf
            <input type="text" name="sigla" placeholder="Sigla do novo setor" required class="w-40 border-gray-300 rounded">
            <input type="text" name="nome" placeholder="Nome por extenso (opcional)" class="flex-1 border-gray-300 rounded">
            <input type="number" name="quota_mb" placeholder="Cota (MB, opcional)" min="0" step="1" class="w-48 border-gray-300 rounded">
            <select name="parent_id" class="border-gray-300 rounded">
                <option value="">Coordenação (opcional)</option>
                @foreach($coordenacoes as $coordenacao)
                    <option value="{{ $coordenacao->id }}">{{ $coordenacao->sigla }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Adicionar</button>
        </form>

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Sigla</th>
                        <th class="p-3">Nome</th>
                        <th class="p-3">Coordenação</th>
                        <th class="p-3">Cota (MB)</th>
                        <th class="p-3">Uso atual</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($setores as $setor)
                        {{-- Um <form> não pode abranger vários <td> de uma <tr> (HTML
                             inválido, navegadores não garantem o envio dos campos) — o
                             form fica fora da linha, e cada campo se associa a ele via
                             o atributo form="", igual ao usado na tela de Usuários. --}}
                        <form id="setor-{{ $setor->id }}-form" method="POST" action="{{ route('admin.setores.update', $setor) }}">
                            @csrf
                            @method('PUT')
                        </form>
                        <tr class="border-t">
                                <td class="p-3">
                                    <input type="text" name="sigla" value="{{ $setor->sigla }}" form="setor-{{ $setor->id }}-form" class="border-gray-300 rounded w-full">
                                </td>
                                <td class="p-3">
                                    <input type="text" name="nome" value="{{ $setor->nome }}" placeholder="Nome por extenso" form="setor-{{ $setor->id }}-form" class="border-gray-300 rounded w-full">
                                </td>
                                <td class="p-3">
                                    <select name="parent_id" form="setor-{{ $setor->id }}-form" class="border-gray-300 rounded">
                                        <option value="">(nenhuma)</option>
                                        @foreach($coordenacoes as $coordenacao)
                                            <option value="{{ $coordenacao->id }}" @selected($setor->parent_id === $coordenacao->id)>{{ $coordenacao->sigla }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-3">
                                    <input type="number" name="quota_mb" min="0" step="1" placeholder="Sem limite" form="setor-{{ $setor->id }}-form"
                                           value="{{ $setor->quota_bytes ? round($setor->quota_bytes / 1048576) : '' }}"
                                           class="border-gray-300 rounded w-32">
                                </td>
                                <td class="p-3 text-sm text-gray-600">
                                    {{ $setor->usoFormatado() }}
                                    @if($setor->percentualUso() !== null)
                                        <span class="text-xs {{ $setor->percentualUso() >= 90 ? 'text-red-600' : 'text-gray-400' }}">({{ $setor->percentualUso() }}%)</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <button type="submit" form="setor-{{ $setor->id }}-form" class="text-blue-600">Salvar</button>
                                    <form action="{{ route('admin.setores.destroy', $setor) }}" method="POST" class="inline" onsubmit="return confirm('Remover este setor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
