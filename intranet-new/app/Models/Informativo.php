<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Informativo extends Model
{
    protected $fillable = ['title', 'content', 'sector_id', 'is_private', 'image', 'published_at'];

    protected $casts = [
        'is_private' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}
