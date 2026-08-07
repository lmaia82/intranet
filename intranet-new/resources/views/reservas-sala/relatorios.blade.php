<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Relatório de Uso das Salas</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 print:py-0">
        <div class="flex justify-between items-center mb-4 print:hidden">
            <a href="{{ route('reservas-sala.index') }}" class="text-blue-600 text-sm">&larr; Voltar</a>
            <button onclick="window.print()" class="px-3 py-1.5 bg-gray-700 text-white rounded text-sm">Imprimir</button>
        </div>

        <form method="GET" class="flex gap-3 items-end mb-6 bg-white shadow rounded p-4 print:hidden">
            <div>
                <label class="block text-sm font-medium">Mês</label>
                <select name="mes" class="mt-1 border-gray-300 rounded">
                    @foreach($mesesNomes as $num => $nome)
                        <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Ano</label>
                <input type="number" name="ano" value="{{ $ano }}" min="2020" max="2050" class="mt-1 border-gray-300 rounded w-24">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Filtrar</button>
        </form>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-xs text-gray-500 uppercase">Total de reservas ({{ $mesesNomes[$mes] }})</p>
                <p class="text-3xl font-bold">{{ $totalEventos }}</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-xs text-gray-500 uppercase">Sala mais utilizada</p>
                <p class="text-3xl font-bold text-green-600">{{ $salaCampea ?? '—' }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold mb-4">Reservas por sala — {{ $mesesNomes[$mes] }}/{{ $ano }}</h3>
            <div class="space-y-3">
                @foreach($salas as $sala)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $sala->nome }}</span>
                            <span class="font-semibold">{{ $sala->total_reservas }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded h-3">
                            <div class="h-3 rounded" style="width: {{ $sala->total_reservas > 0 ? max(4, round($sala->total_reservas / $maiorTotal * 100)) : 0 }}%; background-color: {{ $sala->cor }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
