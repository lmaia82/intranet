<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Salas físicas do CETEM disponíveis para reserva. Importado do sistema
     * de reserva de salas desenvolvido pelo setor de informática (SEIN) —
     * mesmos nomes, cores e regra de sala restrita (Pontes de Miranda e
     * Sala COPTM exigem autorização prévia da chefia/coordenação).
     */
    public function up(): void {
        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['sala', 'auditorio'])->default('sala');
            $table->string('cor', 20)->default('#0d6efd');
            $table->unsignedInteger('capacidade')->nullable();
            $table->string('formacao')->nullable();
            $table->string('equipamentos_fixos')->nullable();
            $table->boolean('restrita')->default(false);
            $table->boolean('permite_arrumacao')->default(false);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });

        $this->seedSalasIniciais();
    }

    public function down(): void {
        Schema::dropIfExists('salas');
    }

    private function seedSalasIniciais(): void
    {
        $now = now();

        $salas = [
            ['nome' => 'Pontes de Miranda', 'capacidade' => 5, 'formacao' => 'Mesa Oval', 'equipamentos_fixos' => 'Sem equipamentos no momento.', 'cor' => '#198754', 'restrita' => true, 'ordem' => 1],
            ['nome' => 'Sala 10', 'capacidade' => 8, 'formacao' => 'Mesa Oval', 'equipamentos_fixos' => 'Sem equipamentos no momento.', 'cor' => '#dc3545', 'restrita' => false, 'ordem' => 2],
            ['nome' => 'Sala Trajano', 'capacidade' => 13, 'formacao' => 'Mesa em U', 'equipamentos_fixos' => 'Computador, Projetor.', 'cor' => '#ffc107', 'restrita' => false, 'ordem' => 3],
            ['nome' => 'Sala Lapido', 'capacidade' => 36, 'formacao' => 'Carteiras móveis', 'equipamentos_fixos' => 'Computador e som.', 'cor' => '#6f42c1', 'restrita' => false, 'ordem' => 4],
            ['nome' => 'Dias Leite (VIP)', 'capacidade' => 15, 'formacao' => 'Mesa em U (Modo Reunião) ou Cadeiras em Fileiras (Modo Palestra, até 35 lugares)', 'equipamentos_fixos' => 'Sem equipamentos no momento.', 'cor' => '#fd7e14', 'restrita' => false, 'permite_arrumacao' => true, 'ordem' => 5],
            ['nome' => 'Sala COPTM', 'descricao' => 'Sala da Coordenação COPTM', 'capacidade' => 5, 'formacao' => 'Mesa Oval', 'equipamentos_fixos' => 'TV 75", Equipamento de Videoconferência, Computador.', 'cor' => '#0dcaf0', 'restrita' => true, 'ordem' => 6],
            ['nome' => 'Auditório Principal', 'tipo' => 'auditorio', 'capacidade' => 120, 'formacao' => 'Cadeiras em fileira (Palestra)', 'equipamentos_fixos' => 'Projetor, Sistema de Som, Câmera PTZ para YouTube.', 'cor' => '#e83e8c', 'restrita' => false, 'ordem' => 7],
        ];

        foreach ($salas as $sala) {
            DB::table('salas')->insert(array_merge([
                'descricao' => null,
                'tipo' => 'sala',
                'restrita' => false,
                'permite_arrumacao' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ], $sala));
        }
    }
};
