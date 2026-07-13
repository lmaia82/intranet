<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grupos de usuários</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.grupos.criar') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo grupo</a>
        </div>

        <div class="space-y-4">
            @forelse($grupos as $grupo)
                <div class="bg-white shadow rounded p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-lg">{{ $grupo->name }}</p>
                            <p class="text-sm text-gray-500 mt-1">
                                @forelse($grupo->permissions as $permissao)
                                    <span class="inline-block bg-gray-100 rounded px-2 py-0.5 mr-1 mb-1 text-xs">{{ $permissao->label }}</span>
                                @empty
                                    <span class="text-gray-400">Nenhuma permissão atribuída</span>
                                @endforelse
                            </p>
                        </div>
                        <div class="flex gap-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.grupos.editar', $grupo) }}" class="text-blue-600">Editar</a>
                            <form action="{{ route('admin.grupos.destroy', $grupo) }}" method="POST" onsubmit="return confirm('Remover este grupo? Usuários associados ficarão sem grupo.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600">Remover</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nenhum grupo cadastrado.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
