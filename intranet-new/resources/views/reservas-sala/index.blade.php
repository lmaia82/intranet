<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reserva de Sala</h2>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8" x-data="{ mapaAberto: false }">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        @if(session('erro'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ session('erro') }}</div>
        @endif

        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ $termo }}" placeholder="Buscar por sala, solicitante, tipo de evento, setor..." class="border-gray-300 rounded text-sm w-72">
                <button type="submit" class="px-3 py-1.5 bg-gray-700 text-white rounded text-sm">Buscar</button>
                @if($termo)
                    <a href="{{ route('reservas-sala.index') }}" class="px-3 py-1.5 border rounded text-sm">Limpar</a>
                @endif
            </form>
            <div class="flex gap-2 items-center">
                <button type="button" @click="mapaAberto = !mapaAberto" class="text-sm text-blue-600">Mapa das Salas</button>
                <a href="{{ route('reservas-sala.relatorios') }}" class="text-sm text-blue-600">Relatórios</a>
                <a href="{{ route('reservas-sala.imprimir-semana') }}" target="_blank" class="text-sm text-blue-600">Imprimir Semana</a>
                @if(auth()->user()->hasPermission('salas.criar'))
                    <a href="{{ route('reservas-sala.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-semibold">+ Nova Reserva</a>
                @endif
            </div>
        </div>

        <div x-show="mapaAberto" x-cloak class="mb-6 bg-white shadow rounded p-4">
            <p class="text-sm text-gray-600 mb-3">Consulte a capacidade e a infraestrutura de cada ambiente antes de reservar.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($salas as $sala)
                    <div class="border rounded p-3" style="border-left: 4px solid {{ $sala->cor }}">
                        <p class="font-bold text-sm">{{ $sala->nome }}@if($sala->restrita) <span class="text-xs text-amber-600 font-normal">(restrita)</span>@endif</p>
                        <p class="text-xs text-gray-600 mt-1">Lugares: {{ $sala->capacidade }} — {{ $sala->formacao }}</p>
                        <p class="text-xs text-gray-500">Equipamentos: {{ $sala->equipamentos_fixos }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if($termo)
            <div class="bg-white shadow rounded p-4">
                <h3 class="font-semibold mb-3">Resultado da busca por "{{ $termo }}"</h3>
                @forelse($reservasEncontradas as $reserva)
                    @include('reservas-sala._linha-reserva', ['reserva' => $reserva])
                @empty
                    <p class="text-sm text-gray-500">Nenhuma reserva encontrada.</p>
                @endforelse
            </div>
        @else
            <div class="bg-white shadow rounded p-4" x-data="{ selecionado: null }">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">{{ $nomeMesAno }}</h3>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('reservas-sala.index', ['mes' => $mesAnterior->month, 'ano' => $mesAnterior->year]) }}" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 text-sm">&#8249;</a>
                        <a href="{{ route('reservas-sala.index') }}" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 text-sm">Hoje</a>
                        <a href="{{ route('reservas-sala.index', ['mes' => $mesProximo->month, 'ano' => $mesProximo->year]) }}" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 text-sm">&#8250;</a>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-500 mb-1">
                    <div>Dom</div><div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
                </div>
                <div class="grid grid-cols-7 gap-1">
                    @foreach($diasCalendario as $diaInfo)
                        @php
                            $chave = $diaInfo['data']->toDateString();
                            $reservasDoDia = $reservasPorDia->get($chave, collect());
                        @endphp
                        <button
                            type="button"
                            @if($reservasDoDia->isNotEmpty())
                                @click="selecionado = (selecionado === '{{ $chave }}' ? null : '{{ $chave }}')"
                            @endif
                            class="aspect-square rounded flex flex-col items-center justify-center text-sm
                                {{ $diaInfo['foraDoMes'] ? 'text-gray-300' : 'text-gray-700' }}
                                {{ $diaInfo['hoje'] ? 'ring-2 ring-blue-500 font-bold' : '' }}
                                {{ $reservasDoDia->isNotEmpty() ? 'hover:bg-blue-50 cursor-pointer' : 'cursor-default' }}"
                        >
                            <span>{{ $diaInfo['data']->day }}</span>
                            @if($reservasDoDia->isNotEmpty())
                                <span class="flex gap-0.5 mt-0.5">
                                    @foreach($reservasDoDia->take(3) as $r)
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $r->sala->cor }}"></span>
                                    @endforeach
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>

                @foreach($reservasPorDia as $chave => $reservasDoDia)
                    <div x-show="selecionado === '{{ $chave }}'" x-cloak class="mt-3 p-3 bg-blue-50 rounded text-sm">
                        <p class="font-semibold mb-2">{{ \Carbon\Carbon::parse($chave)->format('d/m/Y') }}</p>
                        <div class="space-y-2">
                            @foreach($reservasDoDia as $reserva)
                                @include('reservas-sala._linha-reserva', ['reserva' => $reserva])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
