<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\ImportableFromLdap;

#[Fillable(['name', 'email', 'password', 'is_admin', 'is_active', 'sector_id', 'group_id', 'ad_guid', 'ad_setor'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, ImportableFromLdap;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'ad_synced_at' => 'datetime',
        ];
    }

    /**
     * Coluna usada pelo LdapRecord para o objectGUID do AD (em vez do
     * padrão "guid"), para manter o prefixo ad_* dos campos importados.
     */
    public function getLdapGuidColumn(): string
    {
        return 'ad_guid';
    }

    /**
     * Coluna usada pelo LdapRecord para o domínio do AD (em vez do
     * padrão "domain"), mesma razão da coluna de GUID acima.
     */
    public function getLdapDomainColumn(): string
    {
        return 'ad_domain';
    }

    /**
     * Compara o setor cadastrado na intranet com o setor bruto trazido do
     * AD. `null` quando o usuário não está vinculado a uma conta do AD.
     */
    public function setorBateComAd(): ?bool
    {
        if (is_null($this->ad_guid)) {
            return null;
        }

        return $this->sector?->sigla === $this->ad_setor;
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function hasPermission(string $key): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->group?->permissions->contains('key', $key) ?? false;
    }
}
