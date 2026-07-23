<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Hierarquia do organograma do CETEM (coordenação => serviços), extraída
     * da página institucional "Quem é Quem". Só atualiza setores que já
     * existem (por sigla) — não cria nada novo. Setores não listados aqui
     * (ex.: SEIN, BIBLIOTECA) continuam no nível raiz até um admin definir
     * o pai manualmente em Admin > Setores.
     */
    private array $hierarquia = [
        'COADM' => ['SECOF', 'SEGRH', 'SECOM'],
        'COAMI' => ['SECAT'],
        'COPGI' => ['SEAGE'],
        'COPTM' => ['SEDTM', 'SEDPI'],
        'COPMA' => ['SEMEX'],
        'CORON' => ['SENES'],
    ];

    public function up(): void
    {
        foreach ($this->hierarquia as $coordenacaoSigla => $servicosSiglas) {
            $coordenacaoId = DB::table('sectors')->where('sigla', $coordenacaoSigla)->value('id');

            if (! $coordenacaoId) {
                continue;
            }

            DB::table('sectors')
                ->whereIn('sigla', $servicosSiglas)
                ->update(['parent_id' => $coordenacaoId]);
        }
    }

    public function down(): void
    {
        $todasSiglas = collect($this->hierarquia)->flatten()->all();

        DB::table('sectors')->whereIn('sigla', $todasSiglas)->update(['parent_id' => null]);
    }
};
