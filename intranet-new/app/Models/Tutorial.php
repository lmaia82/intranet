<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $table = 'tutoriais';

    protected $fillable = ['titulo', 'data', 'youtube_url'];

    protected $casts = [
        'data' => 'date',
    ];
}
