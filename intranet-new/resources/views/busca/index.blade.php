<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Busca</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <form method="GET" action="{{ route('busca.index') }}" class="bg-white shadow rounded p-4">
            <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por pessoas, documentos, ramais, informativos..."
                class="block w-full border-gray-300 rounded" autofocus>
        </form>

        @if($q === '')
            <p class="text-gray-500">Digite um termo para buscar em Ramais, Informativos, Agenda, Tutoriais e Repositório.</p>
        @elseif(collect($resultados)->every(fn ($lista) => $lista->isEmpty()))
            <p class="text-gray-500">Nenhum resultado para "{{ $q }}".</p>
        @else
            @if(!empty($resultados['ramais']) && $resultados['ramais']->isNotEmpty())
                <div class="bg-white shadow rounded p-4">
                    <h3 class="font-semibold text-lg mb-3">☎️ Ramais</h3>
                    <div class="space-y-2">
                        @foreach($resultados['ramais'] as $telefone)
                            <div class="border-b pb-2 last:border-0">
                                <a href="{{ route('telefones.index') }}" class="font-medium text-blue-700">{{ $telefone->nome }}</a>
                                <p class="text-xs text-gray-500">{{ $telefone->telefone }} — {{ $telefone->cargo }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($resultados['informativos']) && $resultados['informativos']->isNotEmpty())
                <div class="bg-white shadow rounded p-4">
                    <h3 class="font-semibold text-lg mb-3">📢 Informativos</h3>
                    <div class="space-y-2">
                        @foreach($resultados['informativos'] as $informativo)
                            <a href="{{ route('informativos.show', $informativo) }}" class="block border-b pb-2 last:border-0">
                                <span class="font-medium text-blue-700">{{ $informativo->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if((!empty($resultados['eventos']) && $resultados['eventos']->isNotEmpty()) || (!empty($resultados['eventos_gravados']) && $resultados['eventos_gravados']->isNotEmpty()))
                <div class="bg-white shadow rounded p-4">
                    <h3 class="font-semibold text-lg mb-3">📅 Agenda</h3>
                    <div class="space-y-2">
                        @foreach($resultados['eventos'] ?? [] as $evento)
                            <a href="{{ route('eventos.index') }}" class="block border-b pb-2 last:border-0">
                                <span class="font-medium text-blue-700">{{ $evento->title }}</span>
                                <span class="text-xs text-gray-500"> — {{ $evento->dt_start->format('d/m/Y') }}</span>
                            </a>
                        @endforeach
                        @foreach($resultados['eventos_gravados'] ?? [] as $gravado)
                            <a href="{{ $gravado->youtube_url }}" target="_blank" rel="noopener" class="block border-b pb-2 last:border-0">
                                <span class="font-medium text-blue-700">{{ $gravado->titulo }}</span>
                                <span class="text-xs text-gray-500"> — evento gravado, {{ $gravado->data->format('d/m/Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($resultados['tutoriais']) && $resultados['tutoriais']->isNotEmpty())
                <div class="bg-white shadow rounded p-4">
                    <h3 class="font-semibold text-lg mb-3">🎬 Tutoriais</h3>
                    <div class="space-y-2">
                        @foreach($resultados['tutoriais'] as $tutorial)
                            <a href="{{ $tutorial->youtube_url }}" target="_blank" rel="noopener" class="block border-b pb-2 last:border-0">
                                <span class="font-medium text-blue-700">{{ $tutorial->titulo }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if((!empty($resultados['pastas']) && $resultados['pastas']->isNotEmpty()) || (!empty($resultados['arquivos']) && $resultados['arquivos']->isNotEmpty()))
                <div class="bg-white shadow rounded p-4">
                    <h3 class="font-semibold text-lg mb-3">🗂️ Repositório</h3>
                    <div class="space-y-2">
                        @foreach($resultados['pastas'] ?? [] as $pasta)
                            <a href="{{ route('repositorio.index', ['pasta' => $pasta->id]) }}" class="block border-b pb-2 last:border-0">
                                📁 {{ $pasta->nome }}
                            </a>
                        @endforeach
                        @foreach($resultados['arquivos'] ?? [] as $arquivo)
                            <a href="{{ route('repositorio.download', $arquivo) }}" class="block border-b pb-2 last:border-0">
                                📄 {{ $arquivo->nome_original }}
                                <span class="text-xs text-gray-500"> — {{ $arquivo->sector->sigla ?? 'Geral' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
