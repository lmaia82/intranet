<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Setores</h2>
    </x-slot>
    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @error('name')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('admin.setores.store') }}" class="bg-white shadow rounded p-4 mb-6 flex gap-2">
            @csrf
            <input type="text" name="name" placeholder="Nome do novo setor" required class="flex-1 border-gray-300 rounded">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Adicionar</button>
        </form>

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Nome</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($setores as $setor)
                        <tr class="border-t">
                            <form method="POST" action="{{ route('admin.setores.update', $setor) }}">
                                @csrf
                                @method('PUT')
                                <td class="p-3">
                                    <input type="text" name="name" value="{{ $setor->name }}" class="border-gray-300 rounded w-full">
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <button type="submit" class="text-blue-600">Salvar</button>
                            </form>
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
