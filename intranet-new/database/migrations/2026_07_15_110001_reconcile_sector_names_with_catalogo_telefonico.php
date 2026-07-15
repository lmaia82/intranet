<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Alinha as siglas de setor com o catálogo telefônico oficial.
     */
    private array $renomeacoes = [
        'COAD' => 'COADM',
        'COAM' => 'COAMI',
        'COPM' => 'COPTM',
        'CPMA' => 'COPMA',
        'CPGI' => 'COPGI',
    ];

    private array $novosSetores = ['SECOM', 'CORON'];

    public function up(): void
    {
        foreach ($this->renomeacoes as $antigo => $novo) {
            DB::table('sectors')->where('name', $antigo)->update(['name' => $novo]);
        }

        foreach ($this->novosSetores as $nome) {
            if (!DB::table('sectors')->where('name', $nome)->exists()) {
                DB::table('sectors')->insert([
                    'name' => $nome,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->renomeacoes as $antigo => $novo) {
            DB::table('sectors')->where('name', $novo)->update(['name' => $antigo]);
        }
    }
};
