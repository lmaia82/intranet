<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destaque extends Model
{
    protected $fillable = ['titulo', 'imagem', 'link', 'ordem', 'ativo'];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }
}
