<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Informativo extends Model
{
    protected $fillable = ['title', 'content', 'sector_id', 'is_private', 'image', 'arquivo_id', 'published_at'];

    protected $casts = [
        'is_private' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function arquivo()
    {
        return $this->belongsTo(Arquivo::class);
    }

    public function envios()
    {
        return $this->hasMany(InformativoEnvio::class)->latest('enviado_em');
    }

    public function imagemUrl(): ?string
    {
        if ($this->arquivo_id) {
            return route('repositorio.arquivos.visualizar', $this->arquivo_id);
        }

        return $this->image ? Storage::url($this->image) : null;
    }
}
