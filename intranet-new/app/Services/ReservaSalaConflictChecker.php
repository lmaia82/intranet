<?php

namespace App\Services;

use App\Models\ReservaSala;
use App\Models\Sala;
use Carbon\Carbon;

/**
 * Reproduz a regra de conflito de horário do sistema de reserva de salas
 * do SEIN, exatamente como estava implementada em SQL no sistema original.
 *
 * Importante: não é uma checagem genérica de "1h de vão entre as duas
 * reservas". A regra original só bloqueia quando: o início da reserva nova
 * cai a menos de 1h do início de uma reserva existente, OU o fim da nova
 * cai a menos de 1h do fim de uma existente, OU a reserva nova (com a
 * margem de 1h somada nas pontas) engloba uma reserva existente inteira.
 * Duas reservas encostadas (ex: uma termina 09:00, a próxima começa às
 * 09:15) podem passar sem alerta, se nem o início nem o fim da nova caírem
 * dentro dessas janelas de 1h — mantido assim de propósito, para não
 * divergir do comportamento já em uso pelo setor.
 *
 * Também compara só a data de início (não o intervalo data..data_termino
 * inteiro de eventos multi-dia) — mesma razão.
 */
class ReservaSalaConflictChecker
{
    private const MARGEM_MINUTOS = 60;

    public static function existeConflito(
        Sala $sala,
        string $data,
        string $horarioInicio,
        string $horarioFim,
        ?int $ignorarReservaId = null
    ): bool {
        $inicio = Carbon::createFromFormat('H:i', substr($horarioInicio, 0, 5));
        $fim = Carbon::createFromFormat('H:i', substr($horarioFim, 0, 5));

        $inicioMenosMargem = $inicio->copy()->subMinutes(self::MARGEM_MINUTOS);
        $inicioMaisMargem = $inicio->copy()->addMinutes(self::MARGEM_MINUTOS);
        $fimMenosMargem = $fim->copy()->subMinutes(self::MARGEM_MINUTOS);
        $fimMaisMargem = $fim->copy()->addMinutes(self::MARGEM_MINUTOS);

        $query = ReservaSala::where('sala_id', $sala->id)->whereDate('data', $data);
        if ($ignorarReservaId) {
            $query->where('id', '!=', $ignorarReservaId);
        }

        foreach ($query->get() as $existente) {
            $existInicio = Carbon::createFromFormat('H:i', $existente->horaInicioFormatada());
            $existFim = Carbon::createFromFormat('H:i', $existente->horaFimFormatada());

            $inicioPertoDeOutraReserva = $existInicio->between($inicioMenosMargem, $inicioMaisMargem);
            $fimPertoDeOutraReserva = $existFim->between($fimMenosMargem, $fimMaisMargem);
            $novaReservaEngloba = $inicioMenosMargem->lt($existInicio) && $fimMaisMargem->gt($existFim);

            if ($inicioPertoDeOutraReserva || $fimPertoDeOutraReserva || $novaReservaEngloba) {
                return true;
            }
        }

        return false;
    }
}
