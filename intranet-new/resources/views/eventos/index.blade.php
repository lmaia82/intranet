<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agenda / Eventos</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        @if(auth()->user()->hasPermission('eventos.criar'))
            <div class="flex justify-end items-center gap-3 mb-4">
                <a href="{{ route('eventos.lote.form') }}" class="text-blue-600 text-sm">Cadastro em lote</a>
                <a href="{{ route('eventos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo evento</a>
            </div>
        @endif

        <h3 class="font-semibold text-lg mb-2">Próximos eventos</h3>
        <div class="space-y-3 mb-8">
            @forelse($proximos as $evento)
                <div id="evento-{{ $evento->id }}" class="bg-white shadow rounded p-4 flex justify-between items-start scroll-mt-4">
                    <div>
                        <p class="font-semibold">{{ $evento->title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $evento->dt_start->format('d/m/Y') }}
                            @if($evento->dt_end && !$evento->dt_end->eq($evento->dt_start)) até {{ $evento->dt_end->format('d/m/Y') }} @endif
                            @if($evento->tm_start) — {{ \Illuminate\Support\Carbon::parse($evento->tm_start)->format('H:i') }} @endif
                            — {{ $evento->local }}
                        </p>
                    </div>
                    @if(auth()->user()->hasPermission('eventos.criar'))
                        <div class="flex gap-2 text-sm">
                            <a href="{{ route('eventos.edit', $evento) }}" class="text-blue-600">Editar</a>
                            <form action="{{ route('eventos.destroy', $evento) }}" method="POST" onsubmit="return confirm('Remover este evento?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600">Remover</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-gray-500">Nenhum evento futuro cadastrado.</p>
            @endforelse
        </div>

        <h3 class="font-semibold text-lg mb-2">Eventos anteriores</h3>
        <div class="space-y-3">
            @forelse($anteriores as $evento)
                <div id="evento-{{ $evento->id }}" class="bg-white shadow rounded p-4 flex justify-between items-start opacity-70 scroll-mt-4">
                    <div>
                        <p class="font-semibold">{{ $evento->title }}</p>
                        <p class="text-sm text-gray-500">{{ $evento->dt_start->format('d/m/Y') }} — {{ $evento->local }}</p>
                    </div>
                    @if(auth()->user()->hasPermission('eventos.criar'))
                        <div class="flex gap-2 text-sm">
                            <a href="{{ route('eventos.edit', $evento) }}" class="text-blue-600">Editar</a>
                            <form action="{{ route('eventos.destroy', $evento) }}" method="POST" onsubmit="return confirm('Remover este evento?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600">Remover</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-gray-500">Nenhum evento anterior.</p>
            @endforelse
        </div>
        <div class="mt-4 mb-8">{{ $anteriores->links() }}</div>

        <div class="flex justify-between items-center mb-2">
            <h3 class="font-semibold text-lg">Eventos gravados</h3>
            @if(auth()->user()->hasPermission('eventos.criar'))
                <div class="flex items-center gap-3">
                    <a href="{{ route('eventos-gravados.lote.form') }}" class="text-blue-600 text-sm">Cadastro em lote</a>
                    <a href="{{ route('eventos-gravados.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Novo evento gravado</a>
                </div>
            @endif
        </div>
        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Data</th>
                        <th class="p-3">Título</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gravados as $gravado)
                        <tr class="border-t">
                            <td class="p-3">{{ $gravado->data->format('d/m/Y') }}</td>
                            <td class="p-3">
                                <a href="{{ $gravado->youtube_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $gravado->titulo }}</a>
                            </td>
                            @if(auth()->user()->hasPermission('eventos.criar'))
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('eventos-gravados.edit', $gravado) }}" class="text-blue-600">Editar</a>
                                    <form action="{{ route('eventos-gravados.destroy', $gravado) }}" method="POST" class="inline" onsubmit="return confirm('Remover este evento gravado?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-3 text-gray-500">Nenhum evento gravado cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
