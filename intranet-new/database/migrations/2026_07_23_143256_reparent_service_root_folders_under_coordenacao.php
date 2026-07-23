<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Antes desta hierarquia existir, cada setor (coordenação ou serviço)
     * tinha sua pasta raiz solta no topo do repositório (ver
     * App\Models\Sector::pastaRaiz). Reparenta a pasta raiz de cada serviço
     * já existente para dentro da pasta raiz da sua coordenação — preserva
     * subpastas e arquivos, só move a árvore inteira de lugar.
     */
    public function up(): void
    {
        $servicos = DB::table('sectors')->whereNotNull('parent_id')->get(['id', 'sigla', 'parent_id']);

        foreach ($servicos as $servico) {
            $pastaServico = DB::table('pastas')->whereNull('parent_id')->where('nome', $servico->sigla)->first();

            if (! $pastaServico) {
                continue;
            }

            $coordenacao = DB::table('sectors')->where('id', $servico->parent_id)->first();

            if (! $coordenacao) {
                continue;
            }

            $pastaCoordenacao = DB::table('pastas')->whereNull('parent_id')->where('nome', $coordenacao->sigla)->first();

            $pastaCoordenacaoId = $pastaCoordenacao?->id ?? DB::table('pastas')->insertGetId([
                'nome' => $coordenacao->sigla,
                'parent_id' => null,
                'sector_id' => $coordenacao->id,
                'is_private' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pastas')->where('id', $pastaServico->id)->update(['parent_id' => $pastaCoordenacaoId]);
        }
    }

    public function down(): void
    {
        $servicosSiglas = DB::table('sectors')->whereNotNull('parent_id')->pluck('sigla');

        DB::table('pastas')->whereIn('nome', $servicosSiglas)->whereNotNull('parent_id')->update(['parent_id' => null]);
    }
};
