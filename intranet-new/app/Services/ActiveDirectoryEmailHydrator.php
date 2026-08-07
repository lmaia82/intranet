<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use LdapRecord\Models\Model as LdapModel;

/**
 * O AD do CETEM está em migração e tem objetos duplicados/desatualizados
 * pra uma mesma pessoa — já vimos um caso real com o atributo "mail"
 * incompleto (só "lgoncalves", sem "@dominio") num desses objetos, o que
 * criava um usuário fantasma na intranet em vez de reconhecer a conta já
 * existente.
 *
 * Em vez de confiar cegamente no "mail" do AD, monta o e-mail a partir do
 * sAMAccountName (o mesmo identificador já usado pra achar o objeto —
 * ActiveDirectoryAuthenticator::bindarEBuscarUsuario) + o domínio
 * configurado. Só cai pro "mail" do AD se não houver samaccountname por
 * algum motivo (não deveria acontecer, mas evita null).
 */
class ActiveDirectoryEmailHydrator
{
    public function handle(LdapModel $object, EloquentModel $eloquent): void
    {
        $eloquent->email = self::montar($object) ?? $object->getFirstAttribute('mail');
    }

    public static function montar(LdapModel $object): ?string
    {
        $samaccountname = $object->getFirstAttribute('samaccountname');

        if (! $samaccountname) {
            return null;
        }

        return $samaccountname.'@'.config('ldap.upn_domain', 'cetem.gov.br');
    }
}
