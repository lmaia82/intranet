<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Informativos / Mural de Avisos</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <form method="GET" class="flex gap-2">
                <select name="sector_id" class="border-gray-300 rounded" onchange="this.form.submit()">
                    <option value="">Todos os setores</option>
                    @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" @selected(request('sector_id') == $sector->id)>{{ $sector->sigla }}</option>
                    @endforeach
                </select>
            </form>
            @if(auth()->user()->hasPermission('informativos.criar'))
                <div class="flex items-center gap-3">
                    <a href="{{ route('informativos.lote.form') }}" class="text-blue-600 text-sm">Cadastro em lote</a>
                    <a href="{{ route('informativos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo informativo</a>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            @forelse($informativos as $informativo)
                <div class="bg-white shadow rounded p-4 flex gap-4">
                    @if($informativo->imagemUrl())
                        <img src="{{ $informativo->imagemUrl() }}" class="w-24 h-24 object-cover rounded">
                    @endif
                    <div class="flex-1">
                        <a href="{{ route('informativos.show', $informativo) }}" class="text-lg font-semibold text-blue-700">{{ $informativo->title }}</a>
                        <p class="text-sm text-gray-500">{{ $informativo->sector->sigla ?? 'Geral' }} — {{ $informativo->published_at?->format('d/m/Y H:i') }}</p>
                        @if(auth()->user()->hasPermission('informativos.criar'))
                            <div class="mt-2 flex gap-2">
                                <a href="{{ route('informativos.edit', $informativo) }}" class="text-blue-600 text-sm">Editar</a>
                                <form action="{{ route('informativos.destroy', $informativo) }}" method="POST" onsubmit="return confirm('Remover este informativo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm">Remover</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nenhum informativo publicado ainda.</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $informativos->links() }}</div>
    </div>
</x-app-layout>
