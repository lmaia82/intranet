<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acesso extends Model
{
    protected $fillable = ['user_id', 'modulo'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
