<div
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 transition-opacity duration-500 ease-in-out"
    x-data
    :class="$store.loginModal.aberto ? 'opacity-0' : 'opacity-100'"
>
    <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4 text-center">
        <p class="text-gray-700 font-medium">Conheça o que a Intranet CETEM oferece</p>
        <p class="text-sm text-gray-500">Entre para acessar todas as funcionalidades abaixo.</p>
    </div>

    <form @submit.prevent="$dispatch('open-modal', 'login')" class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
        <input type="text" placeholder="🔎 Buscar por pessoas, documentos, ramais, informativos..."
            class="block w-full border-gray-300 rounded" @focus="$dispatch('open-modal', 'login')" readonly>
    </form>

    @if($destaques->isNotEmpty())
    <div
        x-data="carrossel({{ $destaques->count() }})"
        x-init="iniciar()"
        class="relative bg-white/80 backdrop-blur-sm shadow rounded overflow-hidden"
    >
        @foreach($destaques as $i => $destaque)
            <div x-show="atual === {{ $i }}" x-cloak>
                <a href="#" @click.prevent="$dispatch('open-modal', 'login')">
                    <img src="{{ $destaque->imagemUrl() }}" alt="{{ $destaque->titulo }}" class="w-full h-auto block">
                </a>
            </div>
        @endforeach

        @if($destaques->count() > 1)
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

    <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
        <h3 class="font-semibold text-lg mb-3">Aplicações</h3>
        <div class="flex flex-wrap gap-3">
            <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="px-4 py-2 bg-blue-600 text-white rounded">📄 Documento Word</a>
            <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="px-4 py-2 bg-green-600 text-white rounded">📊 Planilha Excel</a>
            <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="px-4 py-2 bg-orange-600 text-white rounded">📽️ Apresentação</a>
            <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="px-4 py-2 bg-red-600 text-white rounded">📕 PDF (Stirling)</a>
            <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="px-4 py-2 bg-gray-200 rounded">🗂️ Repositório de Arquivos</a>
            <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="px-4 py-2 bg-gray-200 rounded">☎️ Ramais</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-lg">📢 Mural de Avisos</h3>
                <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="text-sm text-blue-600">Ver todos</a>
            </div>
            <div class="space-y-3">
                @forelse($informativos as $informativo)
                    <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="block border-b pb-2 last:border-0">
                        <p class="font-medium text-blue-700">{{ $informativo->title }}</p>
                        <p class="text-xs text-gray-500">{{ $informativo->sector->sigla ?? 'Geral' }} — {{ $informativo->published_at?->format('d/m/Y') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Nenhum informativo publicado ainda.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-lg">📅 Agenda</h3>
                <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="text-sm text-blue-600">Ver todos</a>
            </div>
            <div class="space-y-3">
                @forelse($eventos as $evento)
                    <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="block border-b pb-2 last:border-0">
                        <p class="font-medium">{{ $evento->title }}</p>
                        <p class="text-xs text-gray-500">{{ $evento->dt_start->format('d/m/Y') }} — {{ $evento->local }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Nenhum evento futuro.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-lg">🔬 Publicações</h3>
                <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="text-sm text-blue-600">Ver mais</a>
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

        <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-lg">🎬 Últimos Tutoriais</h3>
                <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="text-sm text-blue-600">Ver todos</a>
            </div>
            <div class="space-y-3">
                @forelse($tutoriais as $tutorial)
                    <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="block border-b pb-2 last:border-0">
                        <p class="font-medium text-blue-700">{{ $tutorial->titulo }}</p>
                        <p class="text-xs text-gray-500">{{ $tutorial->data->format('d/m/Y') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Nenhum tutorial publicado ainda.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-lg">🎥 Últimos Eventos Gravados</h3>
                <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="text-sm text-blue-600">Ver todos</a>
            </div>
            <div class="space-y-3">
                @forelse($eventosGravados as $gravado)
                    <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="block border-b pb-2 last:border-0">
                        <p class="font-medium text-blue-700">{{ $gravado->titulo }}</p>
                        <p class="text-xs text-gray-500">{{ $gravado->data->format('d/m/Y') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Nenhum evento gravado ainda.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm shadow rounded p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-lg">📁 Documentos públicos recentes</h3>
                <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="text-sm text-blue-600">Ver repositório</a>
            </div>
            <div class="space-y-3">
                @forelse($documentosPublicos as $documento)
                    <a href="#" @click.prevent="$dispatch('open-modal', 'login')" class="block border-b pb-2 last:border-0">
                        <p class="font-medium text-blue-700">{{ $documento->nome_original }}</p>
                        <p class="text-xs text-gray-500">{{ $documento->sector->sigla ?? 'Geral' }} — {{ $documento->created_at->format('d/m/Y') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Nenhum documento público ainda.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
