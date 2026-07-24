<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cadastro em Lote de Arquivos</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6 space-y-4">
            <p class="text-sm text-gray-600">
                Baixe o modelo de planilha CSV, preencha uma linha
                por arquivo (coluna <strong>arquivo</strong> deve ter o nome exato do arquivo que será
                selecionado abaixo; <strong>pasta</strong> é obrigatória (não é permitido enviar arquivos para
                a raiz), aceita caminhos aninhados separados por "/", ex: <em>Notas Fiscais/2024</em>, e é
                criada automaticamente se não existir; <strong>setor</strong>
                deve corresponder a um setor já cadastrado; <strong>data</strong> no formato dd/mm/aaaa;
                <strong>publico</strong> "sim" ou "nao") e envie o CSV junto com os arquivos correspondentes.
            </p>

            @if(session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded text-sm">{{ session('status') }}</div>
            @endif

            <a href="{{ route('repositorio.arquivos.lote.template') }}" class="inline-block px-4 py-2 bg-gray-200 rounded">Baixar modelo CSV</a>

            @if(session('erros_lote') && count(session('erros_lote')))
                <div class="p-4 bg-red-100 text-red-800 rounded text-sm">
                    <p class="font-semibold mb-1">Alguns arquivos não foram importados:</p>
                    <ul class="list-disc list-inside">
                        @foreach(session('erros_lote') as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('repositorio.arquivos.lote.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Arquivo CSV</label>
                    <input type="file" name="csv" accept=".csv,text/csv" required class="mt-1 block w-full">
                    @error('csv') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div x-data="{ modo: 'arquivos' }">
                    <label class="block text-sm font-medium">Arquivos</label>
                    <div class="flex gap-4 mt-1 mb-2 text-sm">
                        <label class="flex items-center gap-1">
                            <input type="radio" name="modo_selecao" value="arquivos" x-model="modo">
                            Selecionar arquivos
                        </label>
                        <label class="flex items-center gap-1">
                            <input type="radio" name="modo_selecao" value="pasta" x-model="modo">
                            Selecionar uma pasta inteira
                        </label>
                    </div>

                    <input type="file" name="arquivos[]" multiple class="mt-1 block w-full"
                            x-show="modo === 'arquivos'" :required="modo === 'arquivos'" :disabled="modo !== 'arquivos'">

                    <input type="file" name="arquivos[]" multiple webkitdirectory directory mozdirectory class="mt-1 block w-full"
                            x-show="modo === 'pasta'" :required="modo === 'pasta'" :disabled="modo !== 'pasta'">

                    <p class="text-xs text-gray-500 mt-1">
                        Selecione todos os arquivos referenciados na coluna "arquivo" do CSV, ou escolha uma
                        pasta inteira (inclusive subpastas) — útil para lotes grandes, com mais de 200 arquivos.
                    </p>
                    @error('arquivos') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Importar</button>
            </form>
        </div>
    </div>
</x-app-layout>
