<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agenda / Eventos</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('eventos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo evento</a>
        </div>

        <h3 class="font-semibold text-lg mb-2">Próximos eventos</h3>
        <div class="space-y-3 mb-8">
            @forelse($proximos as $evento)
                <div class="bg-white shadow rounded p-4 flex justify-between items-start">
                    <div>
                        <p class="font-semibold">{{ $evento->title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $evento->dt_start->format('d/m/Y') }}
                            @if($evento->dt_end && !$evento->dt_end->eq($evento->dt_start)) até {{ $evento->dt_end->format('d/m/Y') }} @endif
                            @if($evento->tm_start) — {{ \Illuminate\Support\Carbon::parse($evento->tm_start)->format('H:i') }} @endif
                            — {{ $evento->local }}
                        </p>
                    </div>
                    <div class="flex gap-2 text-sm">
                        <a href="{{ route('eventos.edit', $evento) }}" class="text-blue-600">Editar</a>
                        <form action="{{ route('eventos.destroy', $evento) }}" method="POST" onsubmit="return confirm('Remover este evento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600">Remover</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nenhum evento futuro cadastrado.</p>
            @endforelse
        </div>

        <h3 class="font-semibold text-lg mb-2">Eventos anteriores</h3>
        <div class="space-y-3">
            @forelse($anteriores as $evento)
                <div class="bg-white shadow rounded p-4 flex justify-between items-start opacity-70">
                    <div>
                        <p class="font-semibold">{{ $evento->title }}</p>
                        <p class="text-sm text-gray-500">{{ $evento->dt_start->format('d/m/Y') }} — {{ $evento->local }}</p>
                    </div>
                    <div class="flex gap-2 text-sm">
                        <a href="{{ route('eventos.edit', $evento) }}" class="text-blue-600">Editar</a>
                        <form action="{{ route('eventos.destroy', $evento) }}" method="POST" onsubmit="return confirm('Remover este evento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600">Remover</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nenhum evento anterior.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $anteriores->links() }}</div>
    </div>
</x-app-layout>
