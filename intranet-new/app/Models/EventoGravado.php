<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoGravado extends Model
{
    protected $fillable = ['titulo', 'data', 'youtube_url'];

    protected $casts = [
        'data' => 'date',
    ];
}
