<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = ['sigla', 'nome', 'quota_bytes', 'parent_id'];

    /**
     * Quando a coordenação de um setor muda (Admin > Setores), a pasta raiz
     * já existente desse setor no repositório precisa se mover junto — sem
     * isso, ela ficaria presa sob a coordenação antiga (ou solta no topo).
     */
    protected static function booted(): void
    {
        static::updated(function (Sector $sector) {
            if ($sector->wasChanged('parent_id')) {
                $sector->reparentarPastaRaiz();
            }
        });
    }

    private function reparentarPastaRaiz(): void
    {
        $pasta = Pasta::where('sector_id', $this->id)->where('nome', $this->sigla)->first();

        if (! $pasta) {
            return;
        }

        $novoParentId = $this->parent ? $this->parent->pastaRaiz()->id : null;

        if ($pasta->parent_id !== $novoParentId) {
            $pasta->update(['parent_id' => $novoParentId]);
        }
    }

    /**
     * A coordenação à qual este setor (serviço) pertence — null quando o
     * próprio setor já é uma coordenação (ou a diretoria).
     */
    public function parent()
    {
        return $this->belongsTo(Sector::class, 'parent_id');
    }

    /**
     * Os serviços subordinados a esta coordenação.
     */
    public function children()
    {
        return $this->hasMany(Sector::class, 'parent_id');
    }

    /**
     * O próprio id mais os dos serviços subordinados — usado para incluir a
     * hierarquia inteira ao restringir e-mail/visibilidade de um informativo
     * por setor: selecionar uma coordenação também alcança seus serviços.
     *
     * @return array<int, int>
     */
    public function idsComSubordinados(): array
    {
        return [$this->id, ...$this->children->pluck('id')->all()];
    }

    /**
     * "Coordenação / Serviço" quando o setor tem uma coordenação pai, ou só
     * a sigla da própria coordenação quando não tem.
     */
    public function caminhoHierarquico(): string
    {
        return $this->parent ? "{$this->parent->sigla} / {$this->sigla}" : $this->sigla;
    }

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

    public function pastaTemporaria(): Pasta
    {
        return Pasta::firstOrCreate(
            ['nome' => 'Temporário', 'parent_id' => $this->pastaRaiz()->id],
            ['sector_id' => $this->id, 'is_private' => true]
        );
    }

    public function pastaImagensInformativos(): Pasta
    {
        return Pasta::firstOrCreate(
            ['nome' => 'Imagens Informativos', 'parent_id' => $this->pastaRaiz()->id],
            ['sector_id' => $this->id, 'is_private' => false]
        );
    }

    public function pastaDestaques(): Pasta
    {
        return Pasta::firstOrCreate(
            ['nome' => 'Destaques', 'parent_id' => $this->pastaRaiz()->id],
            ['sector_id' => $this->id, 'is_private' => false]
        );
    }

    private function pastaRaiz(): Pasta
    {
        // Setor "serviço" (tem coordenação pai): sua pasta raiz fica dentro
        // da pasta raiz da coordenação, não solta na raiz do repositório.
        $pastaPaiId = $this->parent ? $this->parent->pastaRaiz()->id : null;

        return Pasta::firstOrCreate(
            ['nome' => $this->sigla, 'parent_id' => $pastaPaiId],
            ['sector_id' => $this->id, 'is_private' => false]
        );
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
