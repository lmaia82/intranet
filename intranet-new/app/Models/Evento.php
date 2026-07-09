<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = ['title', 'local', 'informacoes', 'dt_start', 'dt_end', 'tm_start', 'tm_end'];

    protected $casts = [
        'dt_start' => 'date',
        'dt_end' => 'date',
    ];
}
