<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar arquivo</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('repositorio.arquivos.update', $arquivo) }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @method('PUT')
            <p class="text-sm text-gray-500">{{ $arquivo->nome_original }}</p>
            <div>
                <label class="block text-sm font-medium">Descrição</label>
                <textarea name="descricao" rows="3" class="mt-1 block w-full border-gray-300 rounded">{{ old('descricao', $arquivo->descricao) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium">Setor</label>
                <select name="sector_id" required class="mt-1 block w-full border-gray-300 rounded">
                    @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" @selected(old('sector_id', $arquivo->sector_id ?? auth()->user()->sector_id) == $sector->id)>{{ $sector->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_private" value="1" id="is_private" @checked(old('is_private', $arquivo->is_private))>
                <label for="is_private" class="text-sm">Restrito ao setor (privado)</label>
            </div>
            <p class="text-xs text-gray-500 -mt-2">Não marcado = arquivo público (visível a todos). Marcado = visível só para usuários do setor selecionado.</p>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Atualizar</button>
        </form>
    </div>
</x-app-layout>
