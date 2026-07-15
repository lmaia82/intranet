<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cadastro em Lote de Eventos</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6 space-y-4">
            <p class="text-sm text-gray-600">
                Baixe o modelo de planilha CSV, preencha uma linha
                por evento (datas no formato dd/mm/aaaa; <strong>dt_end</strong>, <strong>tm_start</strong>,
                <strong>tm_end</strong> e <strong>informacoes</strong> são opcionais) e envie o arquivo.
            </p>

            @if(session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded text-sm">{{ session('status') }}</div>
            @endif

            <a href="{{ route('eventos.lote.template') }}" class="inline-block px-4 py-2 bg-gray-200 rounded">Baixar modelo CSV</a>

            @if(session('erros_lote') && count(session('erros_lote')))
                <div class="p-4 bg-red-100 text-red-800 rounded text-sm">
                    <p class="font-semibold mb-1">Alguns eventos não foram importados:</p>
                    <ul class="list-disc list-inside">
                        @foreach(session('erros_lote') as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('eventos.lote.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Arquivo CSV</label>
                    <input type="file" name="csv" accept=".csv,text/csv" required class="mt-1 block w-full">
                    @error('csv') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Importar</button>
            </form>
        </div>
    </div>
</x-app-layout>
