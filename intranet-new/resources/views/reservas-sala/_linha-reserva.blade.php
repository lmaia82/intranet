<div class="flex justify-between items-center border-b last:border-0 pb-2 mb-2 last:pb-0 last:mb-0">
    <div>
        <a href="{{ route('reservas-sala.show', $reserva) }}" class="font-medium text-blue-700 hover:underline">{{ $reserva->titulo }}</a>
        <p class="text-xs text-gray-500">
            <span class="inline-block w-2 h-2 rounded-full align-middle" style="background-color: {{ $reserva->sala->cor }}"></span>
            {{ $reserva->sala->nome }} — {{ $reserva->data->format('d/m/Y') }}
            @if($reserva->multiplosDias()) a {{ $reserva->dataFim()->format('d/m/Y') }} @endif
            · {{ $reserva->horaInicioFormatada() }} às {{ $reserva->horaFimFormatada() }}
            · {{ $reserva->solicitante }}
        </p>
    </div>
    @if($reserva->pertenceA(auth()->user()))
        <div class="flex gap-2 text-xs shrink-0">
            <a href="{{ route('reservas-sala.edit', $reserva) }}" class="text-amber-600">Editar</a>
        </div>
    @endif
</div>
