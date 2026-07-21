<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mover arquivo</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('repositorio.arquivos.mover', $arquivo) }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @method('PUT')
            <p class="text-sm text-gray-500">{{ $arquivo->nome_original }}</p>
            <div>
                <label class="block text-sm font-medium">Pasta de destino</label>
                <select name="pasta_id" required class="mt-1 block w-full border-gray-300 rounded">
                    <option value="" disabled @selected(!old('pasta_id', $arquivo->pasta_id))>Selecione uma pasta</option>
                    @foreach($pastasParaSelecao as $opcao)
                        <option value="{{ $opcao['id'] }}" @selected(old('pasta_id', $arquivo->pasta_id) == $opcao['id'])>{{ $opcao['caminho'] }}</option>
                    @endforeach
                </select>
                @error('pasta_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Mover</button>
                <a href="{{ route('repositorio.index', $arquivo->pasta_id ? ['pasta' => $arquivo->pasta_id] : []) }}" class="text-gray-600">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
