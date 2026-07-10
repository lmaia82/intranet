<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arquivo extends Model
{
    protected $fillable = ['pasta_id', 'nome_original', 'caminho', 'extensao', 'tamanho', 'descricao', 'sector_id', 'is_private'];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function pasta()
    {
        return $this->belongsTo(Pasta::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function tamanhoFormatado()
    {
        $bytes = $this->tamanho;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
