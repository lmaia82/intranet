<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = ['previa_login_ativa', 'tempo_inatividade_minutos', 'tutoriais_ativo'];

    protected $casts = [
        'previa_login_ativa' => 'boolean',
        'tempo_inatividade_minutos' => 'integer',
        'tutoriais_ativo' => 'boolean',
    ];

    public static function atual(): self
    {
        return static::firstOrCreate([], [
            'previa_login_ativa' => false,
            'tempo_inatividade_minutos' => 120,
            'tutoriais_ativo' => true,
        ]);
    }
}
