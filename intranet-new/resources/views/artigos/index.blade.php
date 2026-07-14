<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Artigos Científicos</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <form method="GET" class="bg-white shadow rounded p-4 mb-4 grid grid-cols-2 md:grid-cols-4 gap-2">
            <input type="text" name="titulo" value="{{ request('titulo') }}" placeholder="Título" class="border-gray-300 rounded">
            <input type="number" name="ano" value="{{ request('ano') }}" placeholder="Ano" class="border-gray-300 rounded">
            <input type="text" name="autores" value="{{ request('autores') }}" placeholder="Autores" class="border-gray-300 rounded">
            <input type="text" name="palavra_chave" value="{{ request('palavra_chave') }}" placeholder="Palavra-chave" class="border-gray-300 rounded">
            <button class="px-3 py-2 bg-gray-200 rounded col-span-2 md:col-span-1">Buscar</button>
        </form>

        @if(auth()->user()->hasPermission('artigos.criar'))
            <div class="flex justify-between items-center mb-4">
                <a href="{{ route('artigos.lote.form') }}" class="text-blue-600">Cadastro em lote</a>
                <a href="{{ route('artigos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo artigo</a>
            </div>
        @endif

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Título</th>
                        <th class="p-3">Ano</th>
                        <th class="p-3">Autores</th>
                        @if(auth()->user()->hasPermission('artigos.criar'))
                            <th class="p-3"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($artigos as $artigo)
                        <tr class="border-t">
                            <td class="p-3"><a href="{{ route('artigos.show', $artigo) }}" class="text-blue-700">{{ $artigo->titulo }}</a></td>
                            <td class="p-3">{{ $artigo->ano }}</td>
                            <td class="p-3">{{ $artigo->autores }}</td>
                            @if(auth()->user()->hasPermission('artigos.criar'))
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('artigos.edit', $artigo) }}" class="text-blue-600">Editar</a>
                                    <form action="{{ route('artigos.destroy', $artigo) }}" method="POST" class="inline" onsubmit="return confirm('Remover este artigo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $artigos->links() }}</div>
    </div>
</x-app-layout>
