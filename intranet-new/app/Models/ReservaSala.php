<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaSala extends Model
{
    protected $fillable = [
        'sala_id', 'user_id', 'sector_id',
        'titulo', 'data', 'data_termino', 'horario_inicio', 'horario_fim',
        'solicitante', 'tipo_evento', 'publico', 'visita_externa', 'observacoes',
        'equipamentos', 'salavirtual', 'wifi_tipo', 'wifi_nome_evento', 'wifi_visitantes',
        'servicos', 'arrumacao',
        'comunicacao', 'divulgacao',
        'permissao_especial',
    ];

    protected $casts = [
        // Formato explícito Y-m-d: sem isso, o Eloquent grava a coluna com
        // hora "00:00:00" embutida, o que quebra comparações de string por
        // igualdade (ex: checagem de conflito de horário) e casos de borda
        // em whereBetween (o último dia do intervalo, gravado como
        // "AAAA-MM-DD 00:00:00", fica lexicograficamente "depois" do limite
        // "AAAA-MM-DD" e é excluído por engano).
        'data' => 'date:Y-m-d',
        'data_termino' => 'date:Y-m-d',
        'visita_externa' => 'boolean',
        'permissao_especial' => 'boolean',
        'equipamentos' => 'array',
        'wifi_visitantes' => 'array',
        'servicos' => 'array',
        'comunicacao' => 'array',
    ];

    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function pertenceA(?User $user): bool
    {
        return $user && ($this->user_id === $user->id || $user->is_admin);
    }

    public function dataFim()
    {
        return $this->data_termino ?? $this->data;
    }

    public function multiplosDias(): bool
    {
        return !$this->dataFim()->isSameDay($this->data);
    }

    public function horaInicioFormatada(): string
    {
        return substr($this->horario_inicio, 0, 5);
    }

    public function horaFimFormatada(): string
    {
        return substr($this->horario_fim, 0, 5);
    }
}
