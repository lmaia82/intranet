<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ramais / Telefones</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <form method="GET" class="flex gap-2">
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome..." class="border-gray-300 rounded">
                <button class="px-3 py-1 bg-gray-200 rounded">Buscar</button>
            </form>
            @if(auth()->user()->hasPermission('ramais.criar'))
                <div class="flex items-center gap-3">
                    <a href="{{ route('telefones.lote.form') }}" class="text-blue-600 text-sm">Cadastro em lote</a>
                    <a href="{{ route('telefones.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo ramal</a>
                </div>
            @endif
        </div>

        <div class="mb-4 flex flex-wrap gap-1">
            @foreach($letras as $letra)
                <a href="{{ route('telefones.index', ['letra' => $letra]) }}" class="px-2 py-1 border rounded text-sm">{{ $letra }}</a>
            @endforeach
        </div>

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Nome</th>
                        <th class="p-3">Ramal</th>
                        <th class="p-3">Setor</th>
                        <th class="p-3">Cargo</th>
                        <th class="p-3">E-mail</th>
                        @if(auth()->user()->hasPermission('ramais.criar'))
                            <th class="p-3"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($telefones as $telefone)
                        <tr class="border-t">
                            <td class="p-3">{{ $telefone->nome }}</td>
                            <td class="p-3">{{ $telefone->telefone }}</td>
                            <td class="p-3">{{ $telefone->sector->name ?? '-' }}</td>
                            <td class="p-3">{{ $telefone->cargo }}</td>
                            <td class="p-3">{{ $telefone->email }}</td>
                            @if(auth()->user()->hasPermission('ramais.criar'))
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('telefones.edit', $telefone) }}" class="text-blue-600">Editar</a>
                                    <form action="{{ route('telefones.destroy', $telefone) }}" method="POST" class="inline" onsubmit="return confirm('Remover este ramal?')">
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

        <div class="mt-4">{{ $telefones->links() }}</div>
    </div>
</x-app-layout>
