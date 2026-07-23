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

    /**
     * Mesma regra usada por Pasta/Arquivo::visivelPara(), com a hierarquia:
     * um informativo restrito a uma coordenação também é visível para
     * usuários dos serviços subordinados a ela.
     */
    public function visivelPara(User $user): bool
    {
        if (! $this->is_private) {
            return true;
        }

        if ($user->is_admin) {
            return true;
        }

        return $this->sector_id !== null && $user->sector_id !== null
            && in_array($user->sector_id, $this->sector->idsComSubordinados());
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
