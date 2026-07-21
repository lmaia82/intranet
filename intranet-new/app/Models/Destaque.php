<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Destaque extends Model
{
    protected $fillable = ['titulo', 'imagem', 'arquivo_id', 'sector_id', 'link', 'ordem', 'ativo', 'inicio_em', 'fim_em'];

    protected $casts = [
        'ativo' => 'boolean',
        'inicio_em' => 'datetime',
        'fim_em' => 'datetime',
    ];

    public function scopeAtivos($query)
    {
        $agora = now();

        return $query->where('ativo', true)
            ->where(fn ($q) => $q->whereNull('inicio_em')->orWhere('inicio_em', '<=', $agora))
            ->where(fn ($q) => $q->whereNull('fim_em')->orWhere('fim_em', '>=', $agora))
            ->orderBy('ordem');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function arquivo()
    {
        return $this->belongsTo(Arquivo::class);
    }

    public function imagemUrl(): ?string
    {
        if ($this->arquivo_id) {
            return route('repositorio.arquivos.visualizar-publico', $this->arquivo_id);
        }

        return $this->imagem ? Storage::url($this->imagem) : null;
    }

    public function expirado(): bool
    {
        return $this->fim_em !== null && $this->fim_em->isPast();
    }
}
