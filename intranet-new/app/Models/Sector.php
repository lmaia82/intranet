<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = ['sigla', 'nome', 'quota_bytes'];

    public function telefones()
    {
        return $this->hasMany(Telefone::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function arquivos()
    {
        return $this->hasMany(Arquivo::class);
    }

    public function usoBytes(): int
    {
        return (int) $this->arquivos()->sum('tamanho');
    }

    public function usoFormatado(): string
    {
        return self::formatarBytes($this->usoBytes());
    }

    public function quotaFormatada(): string
    {
        return $this->quota_bytes ? self::formatarBytes($this->quota_bytes) : 'Sem limite';
    }

    public function percentualUso(): ?float
    {
        if (!$this->quota_bytes) {
            return null;
        }

        return round(min(100, $this->usoBytes() / $this->quota_bytes * 100), 1);
    }

    public function quotaExcedida(int $bytesAdicionais = 0): bool
    {
        if (!$this->quota_bytes) {
            return false;
        }

        return ($this->usoBytes() + $bytesAdicionais) > $this->quota_bytes;
    }

    public static function formatarBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
