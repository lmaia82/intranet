<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use LdapRecord\Models\Attributes\DistinguishedName;
use LdapRecord\Models\Model as LdapModel;

/**
 * O AD do CETEM organiza cada setor como uma OU própria (ex.:
 * OU=SEIN,OU=CETEM,DC=mineral,DC=cetem) em vez de usar um atributo como
 * "department" (confirmado vazio num usuário real). A OU imediatamente
 * acima do usuário é a sigla do setor.
 */
class ActiveDirectorySetorHydrator
{
    public function handle(LdapModel $object, EloquentModel $eloquent): void
    {
        $unidadesOrganizacionais = DistinguishedName::make($object->getDn())->assoc()['ou'] ?? [];

        $eloquent->ad_setor = $unidadesOrganizacionais[0] ?? null;
    }
}
