<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = [
        'previa_login_ativa',
        'tempo_inatividade_minutos',
        'tutoriais_ativo',
        'sso_inatividade_minutos',
        'sso_duracao_maxima_horas',
        'sso_exigir_login_ao_fechar_navegador',
    ];

    protected $casts = [
        'previa_login_ativa' => 'boolean',
        'tempo_inatividade_minutos' => 'integer',
        'tutoriais_ativo' => 'boolean',
        'sso_inatividade_minutos' => 'integer',
        'sso_duracao_maxima_horas' => 'integer',
        'sso_exigir_login_ao_fechar_navegador' => 'boolean',
    ];

    public static function atual(): self
    {
        return static::firstOrCreate([], [
            'previa_login_ativa' => false,
            'tempo_inatividade_minutos' => 120,
            'tutoriais_ativo' => true,
            'sso_inatividade_minutos' => 30,
            'sso_duracao_maxima_horas' => 8,
            'sso_exigir_login_ao_fechar_navegador' => true,
        ]);
    }
}
