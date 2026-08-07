<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reserva_salas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sala_id')->constrained('salas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->nullOnDelete();

            $table->string('titulo');
            $table->date('data');
            $table->date('data_termino')->nullable();
            $table->time('horario_inicio');
            $table->time('horario_fim');

            $table->string('solicitante')->nullable();
            $table->string('tipo_evento')->nullable();
            $table->enum('publico', ['presencial', 'virtual', 'hibrido'])->nullable();
            $table->boolean('visita_externa')->default(false);
            $table->text('observacoes')->nullable();

            // Checklists reproduzidos do sistema original (SEIN/SESG/COPGI) —
            // guardados como JSON por serem seleções múltiplas sem
            // necessidade de consulta relacional própria.
            $table->json('equipamentos')->nullable();
            $table->string('salavirtual')->nullable();
            $table->string('wifi_tipo')->nullable();
            $table->string('wifi_nome_evento')->nullable();
            $table->json('wifi_visitantes')->nullable();

            $table->json('servicos')->nullable();
            $table->string('arrumacao')->nullable();

            $table->json('comunicacao')->nullable();
            $table->string('divulgacao')->nullable();

            // Declaração de autorização da chefia, exigida apenas para
            // salas restritas (ver Sala::restrita).
            $table->boolean('permissao_especial')->default(false);

            $table->timestamps();

            $table->index(['sala_id', 'data']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('reserva_salas');
    }
};
