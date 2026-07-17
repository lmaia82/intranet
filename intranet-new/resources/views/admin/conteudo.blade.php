<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Monitoramento de Conteúdo</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded p-4">
                <h3 class="font-semibold text-lg mb-4">📖 Informativos mais lidos (30 dias)</h3>
                @forelse($informativosMaisLidos as $registro)
                    <div class="flex items-center gap-3 text-sm mb-2">
                        <a href="{{ route('informativos.show', $registro->item) }}" class="w-40 shrink-0 truncate text-blue-700 hover:underline" title="{{ $registro->item->title }}">{{ $registro->item->title }}</a>
                        <div class="flex-1 bg-gray-100 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full" style="width: {{ max(4, round($registro->total / $maxInformativo * 100)) }}%"></div>
                        </div>
                        <span class="w-8 shrink-0 text-right font-medium text-gray-700">{{ $registro->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Nenhuma leitura registrada nos últimos 30 dias.</p>
                @endforelse
            </div>

            <div class="bg-white shadow rounded p-4">
                <h3 class="font-semibold text-lg mb-4">📁 Arquivos mais baixados (30 dias)</h3>
                @forelse($arquivosMaisBaixados as $registro)
                    <div class="flex items-center gap-3 text-sm mb-2">
                        <span class="w-40 shrink-0 truncate text-gray-700" title="{{ $registro->item->nome_original }}">{{ $registro->item->nome_original }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full" style="width: {{ max(4, round($registro->total / $maxArquivo * 100)) }}%"></div>
                        </div>
                        <span class="w-8 shrink-0 text-right font-medium text-gray-700">{{ $registro->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Nenhum download registrado nos últimos 30 dias.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-4">🔍 Termos mais buscados (30 dias)</h3>
            @forelse($termosMaisBuscados as $registro)
                <div class="flex items-center gap-3 text-sm mb-2">
                    <span class="w-40 shrink-0 truncate text-gray-700" title="{{ $registro->termo }}">{{ $registro->termo }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ max(4, round($registro->total / $maxTermo * 100)) }}%"></div>
                    </div>
                    <span class="w-8 shrink-0 text-right font-medium text-gray-700">{{ $registro->total }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nenhuma busca registrada nos últimos 30 dias.</p>
            @endforelse
        </div>

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-2">❌ Buscas sem resultado (30 dias)</h3>
            <p class="text-xs text-gray-500 mb-4">Termos que os usuários procuraram e não encontraram nada — bons candidatos a lacunas de conteúdo.</p>
            @forelse($buscasSemResultado as $registro)
                <div class="flex items-center justify-between text-sm border-b py-2 last:border-0">
                    <span class="text-gray-800">"{{ $registro->termo }}"</span>
                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-orange-100 text-orange-800">{{ $registro->total }}x</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nenhuma busca sem resultado nos últimos 30 dias.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
