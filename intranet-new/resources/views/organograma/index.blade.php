<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Organograma</h2>
    </x-slot>
    <div class="py-6 max-w-full mx-auto sm:px-6 lg:px-8">
        <p class="text-sm text-gray-500 mb-4">
            Reflete a hierarquia definida em Admin &gt; Setores. Para corrigir uma coordenação ou
            incluir/remover um serviço, ajuste por lá.
        </p>

        <div class="bg-white shadow rounded p-6 overflow-x-auto">
            <div class="flex flex-col items-center min-w-max">
                @if($diretoria)
                    <div class="relative">
                        <div class="bg-[#166F9E] text-white font-bold rounded px-8 py-3 shadow text-center">
                            {{ $diretoria->nome ?: $diretoria->sigla }}
                        </div>
                        <div class="absolute top-1/2 -translate-y-1/2 left-full flex items-center gap-2 ml-6">
                            <div class="w-6 h-px bg-gray-300"></div>
                            <div class="bg-yellow-100 border border-yellow-400 rounded-full px-4 py-2 text-center text-xs text-gray-700 whitespace-nowrap">
                                CTC - Conselho Técnico Científico
                            </div>
                        </div>
                    </div>
                    @if($coordenacoes->isNotEmpty())
                        <div class="w-px h-6 bg-gray-300"></div>
                    @endif
                @endif

                @if($coordenacoes->isNotEmpty())
                    <div class="flex">
                        @foreach($coordenacoes as $coordenacao)
                            <div class="flex flex-col items-center px-3" style="min-width: 200px;">
                                <div class="w-full border-t-2 border-gray-300 relative h-6">
                                    <div class="absolute left-1/2 -translate-x-1/2 top-0 w-px h-6 bg-gray-300"></div>
                                </div>

                                <p class="font-bold text-[#166F9E] text-sm">{{ $coordenacao->sigla }}</p>
                                <div class="bg-orange-100 border border-orange-300 rounded p-2 text-center text-xs w-full mt-1">
                                    {{ $coordenacao->nome ?: $coordenacao->sigla }}
                                </div>

                                @if($coordenacao->children->isNotEmpty())
                                    <div class="w-px h-6 bg-gray-300"></div>
                                    <div class="w-full space-y-1">
                                        @foreach($coordenacao->children->sortBy('sigla') as $servico)
                                            <div class="bg-blue-50 border border-blue-200 rounded p-2 text-center text-xs">
                                                {{ $servico->nome ?: $servico->sigla }} - {{ $servico->sigla }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(!$diretoria && $coordenacoes->isEmpty())
                    <p class="text-sm text-gray-500">Nenhum setor cadastrado ainda.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
