<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bem-vindo(a), {{ auth()->user()->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if($destaques->isNotEmpty())
        <div
            x-data="{ atual: 0, total: {{ $destaques->count() }} }"
            x-init="setInterval(() => { atual = (atual + 1) % total }, 5000)"
            class="relative bg-white shadow rounded overflow-hidden"
        >
            @foreach($destaques as $i => $destaque)
                <div x-show="atual === {{ $i }}" x-cloak>
                    @if($destaque->link)
                        <a href="{{ $destaque->link }}" target="_blank" rel="noopener">
                            <img src="{{ Storage::url($destaque->imagem) }}" alt="{{ $destaque->titulo }}" class="w-full h-auto block">
                        </a>
                    @else
                        <img src="{{ Storage::url($destaque->imagem) }}" alt="{{ $destaque->titulo }}" class="w-full h-auto block">
                    @endif
                </div>
            @endforeach

            @if($destaques->count() > 1)
                <button @click="atual = (atual - 1 + total) % total" type="button"
                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full w-8 h-8 flex items-center justify-center shadow">
                    &#8249;
                </button>
                <button @click="atual = (atual + 1) % total" type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full w-8 h-8 flex items-center justify-center shadow">
                    &#8250;
                </button>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2">
                    @foreach($destaques as $i => $destaque)
                        <button @click="atual = {{ $i }}" type="button"
                            :class="atual === {{ $i }} ? 'bg-white' : 'bg-white/50'"
                            class="w-2.5 h-2.5 rounded-full"></button>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-3">Aplicações</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('onlyoffice.aplicacoes') }}" class="px-4 py-2 bg-blue-600 text-white rounded">📄 Documento Word</a>
                <a href="{{ route('onlyoffice.aplicacoes') }}" class="px-4 py-2 bg-green-600 text-white rounded">📊 Planilha Excel</a>
                <a href="{{ route('onlyoffice.aplicacoes') }}" class="px-4 py-2 bg-orange-600 text-white rounded">📽️ Apresentação</a>
                <a href="{{ route('repositorio.index') }}" class="px-4 py-2 bg-gray-200 rounded">🗂️ Repositório de Arquivos</a>
                <a href="{{ route('telefones.index') }}" class="px-4 py-2 bg-gray-200 rounded">☎️ Ramais</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-4" x-data="{ selecionado: null }">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-lg">🗓️ {{ $nomeMesAno }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard', ['mes' => $mesAnterior->month, 'ano' => $mesAnterior->year]) }}" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">&#8249;</a>
                    <a href="{{ route('dashboard') }}" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 text-xs">Hoje</a>
                    <a href="{{ route('dashboard', ['mes' => $mesProximo->month, 'ano' => $mesProximo->year]) }}" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">&#8250;</a>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-500 mb-1">
                <div>Dom</div><div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
            </div>
            <div class="grid grid-cols-7 gap-1">
                @foreach($diasCalendario as $diaInfo)
                    @php
                        $chave = $diaInfo['data']->toDateString();
                        $eventosDoDia = $eventosPorDia->get($chave, collect());
                    @endphp
                    <button
                        type="button"
                        @if($eventosDoDia->isNotEmpty())
                            @click="selecionado = (selecionado === '{{ $chave }}' ? null : '{{ $chave }}')"
                        @endif
                        class="aspect-square rounded flex flex-col items-center justify-center text-sm
                            {{ $diaInfo['foraDoMes'] ? 'text-gray-300' : 'text-gray-700' }}
                            {{ $diaInfo['hoje'] ? 'ring-2 ring-blue-500 font-bold' : '' }}
                            {{ $eventosDoDia->isNotEmpty() ? 'hover:bg-blue-50 cursor-pointer' : 'cursor-default' }}"
                    >
                        <span>{{ $diaInfo['data']->day }}</span>
                        @if($eventosDoDia->isNotEmpty())
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-0.5"></span>
                        @endif
                    </button>
                @endforeach
            </div>

            @foreach($eventosPorDia as $chave => $eventosDoDia)
                <div x-show="selecionado === '{{ $chave }}'" x-cloak class="mt-3 p-3 bg-blue-50 rounded text-sm">
                    <p class="font-semibold mb-1">{{ \Carbon\Carbon::parse($chave)->format('d/m/Y') }}</p>
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach($eventosDoDia as $evento)
                            <li>{{ $evento->title }}@if($evento->local) — {{ $evento->local }}@endif</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('eventos.index') }}" class="text-blue-600 text-xs">Ver na Agenda &rarr;</a>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">📢 Mural de Avisos</h3>
                    <a href="{{ route('informativos.index') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($informativos as $informativo)
                        <a href="{{ route('informativos.show', $informativo) }}" class="block border-b pb-2 last:border-0">
                            <p class="font-medium text-blue-700">{{ $informativo->title }}</p>
                            <p class="text-xs text-gray-500">{{ $informativo->sector->name ?? 'Geral' }} — {{ $informativo->published_at?->format('d/m/Y') }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum informativo publicado ainda.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">📅 Agenda</h3>
                    <a href="{{ route('eventos.index') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($eventos as $evento)
                        <div class="border-b pb-2 last:border-0">
                            <p class="font-medium">{{ $evento->title }}</p>
                            <p class="text-xs text-gray-500">{{ $evento->dt_start->format('d/m/Y') }} — {{ $evento->local }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum evento futuro.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">🔬 Publicações</h3>
                    <a href="{{ route('artigos.index') }}" class="text-sm text-blue-600">Ver mais</a>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                    Os artigos científicos do CETEM estão disponíveis no Mineralis,
                    o repositório institucional, e no Master, a biblioteca digital
                    de produção técnico-científica publicada por editores externos.
                </p>
                <div class="flex flex-col gap-1">
                    <a href="https://mineralis.cetem.gov.br/buscar" target="_blank" rel="noopener" class="text-sm text-blue-600 font-medium">
                        Acessar o Mineralis &rarr;
                    </a>
                    <a href="https://master.cetem.gov.br/" target="_blank" rel="noopener" class="text-sm text-blue-600 font-medium">
                        Acessar o Master &rarr;
                    </a>
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">📁 Meus últimos arquivos</h3>
                    <a href="{{ route('repositorio.meus') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($meusArquivos as $arquivo)
                        <div class="flex justify-between items-center border-b pb-2 last:border-0">
                            <span>{{ $arquivo->nome_original }}</span>
                            @if(in_array($arquivo->extensao, ['doc','docx','odt','xls','xlsx','ods','ppt','pptx','odp','pdf']))
                                <a href="{{ route('onlyoffice.editor', $arquivo) }}" class="text-sm text-green-700">abrir</a>
                            @else
                                <a href="{{ route('repositorio.download', $arquivo) }}" class="text-sm text-blue-700">baixar</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum arquivo por aqui ainda.</p>
                    @endforelse
                </div>
            </div>

            @if(auth()->user()->hasPermission('tutoriais.ver'))
            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">🎬 Últimos Tutoriais</h3>
                    <a href="{{ route('tutoriais.index') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($tutoriais as $tutorial)
                        <a href="{{ $tutorial->youtube_url }}" target="_blank" rel="noopener" class="block border-b pb-2 last:border-0">
                            <p class="font-medium text-blue-700">{{ $tutorial->titulo }}</p>
                            <p class="text-xs text-gray-500">{{ $tutorial->data->format('d/m/Y') }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum tutorial publicado ainda.</p>
                    @endforelse
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('eventos.ver'))
            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">🎥 Últimos Eventos Gravados</h3>
                    <a href="{{ route('eventos.index') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($eventosGravados as $gravado)
                        <a href="{{ $gravado->youtube_url }}" target="_blank" rel="noopener" class="block border-b pb-2 last:border-0">
                            <p class="font-medium text-blue-700">{{ $gravado->titulo }}</p>
                            <p class="text-xs text-gray-500">{{ $gravado->data->format('d/m/Y') }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum evento gravado ainda.</p>
                    @endforelse
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('repositorio.ver'))
            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">📁 Documentos públicos recentes</h3>
                    <a href="{{ route('repositorio.index') }}" class="text-sm text-blue-600">Ver repositório</a>
                </div>
                <div class="space-y-3">
                    @forelse($documentosPublicos as $documento)
                        <a href="{{ route('repositorio.download', $documento) }}" class="block border-b pb-2 last:border-0">
                            <p class="font-medium text-blue-700">{{ $documento->nome_original }}</p>
                            <p class="text-xs text-gray-500">{{ $documento->sector->name ?? 'Geral' }} — {{ $documento->created_at->format('d/m/Y') }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum documento público ainda.</p>
                    @endforelse
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
