<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapeamentoSetorAd extends Model
{
    protected $table = 'mapeamentos_setor_ad';

    protected $fillable = ['ad_setor', 'sector_id'];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}
