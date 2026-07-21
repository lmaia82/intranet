<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasta extends Model
{
    protected $fillable = ['nome', 'parent_id', 'sector_id', 'is_private'];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function visivelPara(User $user): bool
    {
        if (!$this->is_private) {
            return true;
        }

        if ($user->is_admin) {
            return true;
        }

        return $this->sector_id !== null && $this->sector_id === $user->sector_id;
    }

    public function parent()
    {
        return $this->belongsTo(Pasta::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Pasta::class, 'parent_id')->orderBy('nome');
    }

    public function arquivos()
    {
        return $this->hasMany(Arquivo::class)->orderBy('nome_original');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function todosArquivosDescendentes()
    {
        $arquivos = $this->arquivos()->get();
        foreach ($this->children as $filha) {
            $arquivos = $arquivos->merge($filha->todosArquivosDescendentes());
        }
        return $arquivos;
    }

    public function breadcrumb()
    {
        $trilha = collect([$this]);
        $atual = $this;
        while ($atual->parent) {
            $atual = $atual->parent;
            $trilha->prepend($atual);
        }
        return $trilha;
    }
}
