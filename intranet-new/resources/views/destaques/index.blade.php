<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Destaques</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        @if(auth()->user()->hasPermission('destaques.criar'))
            <div class="flex justify-end mb-4">
                <a href="{{ route('destaques.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo destaque</a>
            </div>
        @endif

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="w-full table-fixed text-left">
                <colgroup>
                    <col class="w-[12%]">
                    <col class="w-[20%]">
                    <col class="w-[30%]">
                    <col class="w-[8%]">
                    <col class="w-[10%]">
                    @if(auth()->user()->hasPermission('destaques.criar'))
                        <col class="w-[20%]">
                    @endif
                </colgroup>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Imagem</th>
                        <th class="p-3">Título</th>
                        <th class="p-3">Período</th>
                        <th class="p-3">Ordem</th>
                        <th class="p-3">Status</th>
                        @if(auth()->user()->hasPermission('destaques.criar'))
                            <th class="p-3"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($destaques as $destaque)
                        <tr class="border-t">
                            <td class="p-3"><img src="{{ $destaque->imagemUrl() }}" class="h-12 rounded border"></td>
                            <td class="p-3 truncate" title="{{ $destaque->titulo }}">{{ $destaque->titulo ?: '—' }}</td>
                            <td class="p-3 text-xs whitespace-nowrap">
                                {{ optional($destaque->inicio_em)->format('d/m/Y H:i') ?? '—' }}
                                até
                                {{ optional($destaque->fim_em)->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="p-3">{{ $destaque->ordem }}</td>
                            <td class="p-3">
                                @if(!$destaque->ativo)
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-600">Inativo</span>
                                @elseif($destaque->expirado())
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-orange-100 text-orange-800">Expirado</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Ativo</span>
                                @endif
                            </td>
                            @if(auth()->user()->hasPermission('destaques.criar'))
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('destaques.edit', $destaque) }}" class="text-blue-600">Editar</a>
                                    <form action="{{ route('destaques.destroy', $destaque) }}" method="POST" class="inline" onsubmit="return confirm('Remover este destaque?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-3 text-gray-500">Nenhum destaque cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
