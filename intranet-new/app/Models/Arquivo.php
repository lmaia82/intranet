<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arquivo extends Model
{
    protected $fillable = ['pasta_id', 'criado_por_id', 'nome_original', 'caminho', 'extensao', 'tamanho', 'descricao', 'data', 'sector_id', 'is_private', 'paperless_document_id', 'conteudo_ocr', 'ocr_status', 'paperless_task_id', 'ocr_erro'];

    protected $casts = [
        'is_private' => 'boolean',
        'data' => 'date',
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

    public function pasta()
    {
        return $this->belongsTo(Pasta::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function destaques()
    {
        return $this->hasMany(Destaque::class);
    }

    public function informativos()
    {
        return $this->hasMany(Informativo::class);
    }

    /**
     * Um arquivo usado como imagem de um destaque ou informativo não pode
     * ser excluído do repositório sem quebrar essa referência — ver
     * RepositorioController::destroyArquivo().
     */
    public function emUso(): bool
    {
        return $this->destaques()->exists() || $this->informativos()->exists();
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
