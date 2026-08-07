<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    protected $fillable = [
        'nome', 'descricao', 'tipo', 'cor', 'capacidade', 'formacao',
        'equipamentos_fixos', 'restrita', 'permite_arrumacao', 'ordem',
    ];

    protected $casts = [
        'restrita' => 'boolean',
        'permite_arrumacao' => 'boolean',
    ];

    public function reservas()
    {
        return $this->hasMany(ReservaSala::class);
    }
}
