<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar pasta</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('repositorio.pastas.update', $pasta) }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium">Nome</label>
                <input type="text" name="nome" value="{{ old('nome', $pasta->nome) }}" class="mt-1 block w-full border-gray-300 rounded">
                @error('nome') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Setor</label>
                <select name="sector_id" class="mt-1 block w-full border-gray-300 rounded">
                    <option value="">(Geral, sem setor)</option>
                    @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" @selected(old('sector_id', $pasta->sector_id) == $sector->id)>{{ $sector->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_private" value="1" id="is_private" @checked(old('is_private', $pasta->is_private))>
                <label for="is_private" class="text-sm">Restrita ao setor (privada)</label>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Atualizar</button>
        </form>
    </div>
</x-app-layout>
