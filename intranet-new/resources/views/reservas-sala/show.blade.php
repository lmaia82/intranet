@php
    $arrumacaoExibicao = match($reserva->arrumacao) {
        'interna' => 'Modo Reunião',
        'externa' => 'Modo Palestra',
        default => 'Não se aplica',
    };
    $salaVirtualExibicao = $reserva->salavirtual ? ucfirst(str_replace('rnp', 'RNP ', $reserva->salavirtual)) : 'Não se aplica';
    $divulgacaoExibicao = $reserva->divulgacao ? ucfirst($reserva->divulgacao) : 'Não se aplica';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalhes da Reserva</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Sala / Auditório</p>
                    <p class="font-bold text-lg" style="color: {{ $reserva->sala->cor }}">{{ $reserva->sala->nome }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Data e Horário</p>
                    <p class="font-bold text-lg text-blue-700">
                        {{ $reserva->data->format('d/m/Y') }}@if($reserva->multiplosDias()) até {{ $reserva->dataFim()->format('d/m/Y') }} @endif
                        | {{ $reserva->horaInicioFormatada() }} às {{ $reserva->horaFimFormatada() }}
                    </p>
                </div>
            </div>

            <p class="font-semibold text-xl mb-4">{{ $reserva->titulo }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded border mb-4 text-sm">
                <div>
                    <p class="mb-2"><strong>Organizador:</strong> {{ $reserva->user->name ?? 'Usuário removido' }}</p>
                    <p class="mb-2"><strong>Solicitante:</strong> {{ $reserva->solicitante }}</p>
                    <p class="mb-0"><strong>Setor/Coordenação:</strong> {{ $reserva->sector?->caminhoHierarquico() ?? '—' }}</p>
                </div>
                <div>
                    <p class="mb-2"><strong>Tipo de Evento:</strong> {{ $reserva->tipo_evento }}</p>
                    <p class="mb-2"><strong>Público:</strong> {{ ucfirst($reserva->publico) }}</p>
                    <p class="mb-0"><strong>Visita externa?</strong> {{ $reserva->visita_externa ? 'Sim' : 'Não' }}</p>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-xs text-gray-500 uppercase border-b pb-1 mb-2">Checklists</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong class="text-blue-700 block mb-1">Equipamentos TI</strong>
                        @foreach($reserva->equipamentos ?? [] as $eq)
                            <span class="inline-block bg-blue-100 text-blue-800 rounded px-2 py-0.5 mr-1 mb-1 text-xs">{{ $eq }}</span>
                        @endforeach
                        <p class="text-xs text-gray-500 mt-2">Plataforma virtual: {{ $salaVirtualExibicao }}</p>
                    </div>
                    <div>
                        <strong class="text-green-700 block mb-1">Serviços Gerais</strong>
                        @foreach($reserva->servicos ?? [] as $sv)
                            <span class="inline-block bg-green-100 text-green-800 rounded px-2 py-0.5 mr-1 mb-1 text-xs">{{ $sv }}</span>
                        @endforeach
                        <p class="text-xs text-gray-500 mt-2">Arrumação: {{ $arrumacaoExibicao }}</p>
                    </div>
                    <div>
                        <strong class="text-red-700 block mb-1">Comunicação</strong>
                        @foreach($reserva->comunicacao ?? [] as $com)
                            <span class="inline-block bg-red-100 text-red-800 rounded px-2 py-0.5 mr-1 mb-1 text-xs">{{ $com }}</span>
                        @endforeach
                        <p class="text-xs text-gray-500 mt-2">Divulgação: {{ $divulgacaoExibicao }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded border">
                <p class="text-xs text-gray-500 uppercase mb-2">Observações</p>
                <p class="text-sm whitespace-pre-wrap">{{ $reserva->observacoes ?: 'Nenhuma observação registrada.' }}</p>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="{{ route('reservas-sala.index') }}" class="text-blue-600 text-sm">&larr; Voltar</a>
                @if($reserva->pertenceA(auth()->user()))
                    <a href="{{ route('reservas-sala.edit', $reserva) }}" class="px-4 py-2 bg-amber-500 text-white rounded text-sm font-semibold">Editar</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
