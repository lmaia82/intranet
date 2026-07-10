<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bem-vindo(a), {{ auth()->user()->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                    <h3 class="font-semibold text-lg">🔬 Últimos Artigos</h3>
                    <a href="{{ route('artigos.index') }}" class="text-sm text-blue-600">Ver todos</a>
                </div>
                <div class="space-y-3">
                    @forelse($artigos as $artigo)
                        <a href="{{ route('artigos.show', $artigo) }}" class="block border-b pb-2 last:border-0">
                            <p class="font-medium text-blue-700">{{ $artigo->titulo }}</p>
                            <p class="text-xs text-gray-500">{{ $artigo->ano }} — {{ $artigo->autores }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum artigo cadastrado ainda.</p>
                    @endforelse
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

        </div>
    </div>
</x-app-layout>
