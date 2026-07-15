<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telefone extends Model
{
    protected $fillable = ['nome', 'unidade', 'telefone', 'sector_id', 'email', 'telefone_externo', 'cargo'];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}
