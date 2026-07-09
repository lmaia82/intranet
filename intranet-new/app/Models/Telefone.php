<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telefone extends Model
{
    protected $fillable = ['nome', 'telefone', 'sector_id', 'email', 'cargo'];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}
