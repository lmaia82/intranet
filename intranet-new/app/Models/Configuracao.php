<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = ['previa_login_ativa'];

    protected $casts = [
        'previa_login_ativa' => 'boolean',
    ];

    public static function atual(): self
    {
        return static::firstOrCreate([], ['previa_login_ativa' => false]);
    }
}
