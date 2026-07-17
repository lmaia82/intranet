<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformativoEnvio extends Model
{
    protected $fillable = ['informativo_id', 'email', 'sucesso', 'erro', 'enviado_em'];

    protected $casts = [
        'enviado_em' => 'datetime',
        'sucesso' => 'boolean',
    ];

    public function informativo()
    {
        return $this->belongsTo(Informativo::class);
    }
}
