<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ramais / Telefones</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
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

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="w-full table-fixed text-left text-sm">
                <colgroup>
                    <col class="w-[18%]">
                    <col class="w-[9%]">
                    <col class="w-[7%]">
                    <col class="w-[9%]">
                    <col class="w-[18%]">
                    <col class="w-[19%]">
                    <col class="w-[11%]">
                    @if(auth()->user()->hasPermission('ramais.criar'))
                        <col class="w-[9%]">
                    @endif
                </colgroup>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 truncate">Nome</th>
                        <th class="p-3 truncate">Unidade</th>
                        <th class="p-3 truncate">Ramal</th>
                        <th class="p-3 truncate">Setor</th>
                        <th class="p-3 truncate">Cargo</th>
                        <th class="p-3 truncate">E-mail</th>
                        <th class="p-3 truncate">Telefone Externo</th>
                        @if(auth()->user()->hasPermission('ramais.criar'))
                            <th class="p-3 pr-6"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($telefones as $telefone)
                        <tr class="border-t">
                            <td class="p-3 truncate" title="{{ $telefone->nome }}">{{ $telefone->nome }}</td>
                            <td class="p-3 truncate" title="{{ $telefone->unidade }}">{{ $telefone->unidade }}</td>
                            <td class="p-3 truncate" title="{{ $telefone->telefone }}">{{ $telefone->telefone }}</td>
                            <td class="p-3 truncate" title="{{ $telefone->sector->sigla ?? '-' }}">{{ $telefone->sector->sigla ?? '-' }}</td>
                            <td class="p-3 truncate" title="{{ $telefone->cargo }}">{{ $telefone->cargo }}</td>
                            <td class="p-3 truncate" title="{{ $telefone->email }}">{{ $telefone->email }}</td>
                            <td class="p-3 truncate" title="{{ $telefone->telefone_externo }}">{{ $telefone->telefone_externo }}</td>
                            @if(auth()->user()->hasPermission('ramais.criar'))
                                <td class="p-3 pr-6 text-right whitespace-nowrap">
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
