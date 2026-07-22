<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LdapRecord\Container;
use LdapRecord\LdapRecordException;
use LdapRecord\Laravel\Import\UserSynchronizer;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

/**
 * Autentica diretamente no AD (bind), sem depender de uma conta de serviço
 * fixa para busca — o CETEM optou por não configurar uma conta de serviço,
 * então cada login se autentica com a própria credencial do usuário, e essa
 * mesma conexão (já autenticada) é reaproveitada para buscar seus dados.
 *
 * Isso significa que não há como importar o diretório inteiro em lote (o
 * comando `ldap:import` agendado precisa de uma conta com acesso a todos os
 * usuários); nome/e-mail/setor só são sincronizados no momento em que o
 * próprio usuário loga.
 */
class ActiveDirectoryAuthenticator
{
    public function __construct(private UserSynchronizer $synchronizer)
    {
    }

    /**
     * Tenta autenticar e sincronizar o usuário a partir do e-mail e senha
     * informados na tela de login. Retorna o usuário local (já salvo) em
     * caso de sucesso, ou null se a senha não confere no AD.
     */
    public function autenticar(string $email, string $password): ?User
    {
        $ldapUser = $this->bindarEBuscarUsuario($email, $password);

        if (! $ldapUser) {
            return null;
        }

        $usuario = $this->synchronizer->run($ldapUser);
        $usuario->ad_synced_at = now();
        $usuario->save();

        return $usuario;
    }

    /**
     * Tenta o bind direto no AD usando os formatos de identidade aceitos
     * pelo Active Directory, sem precisar de uma conta de serviço:
     * o e-mail como UPN, e o formato down-level "NETBIOS\usuario" (mesmo
     * padrão usado pela integração já em produção no GLPI do CETEM).
     */
    protected function bindarEBuscarUsuario(string $email, string $password): ?LdapUser
    {
        $connection = Container::getConnection('default');

        foreach ($this->possiveisIdentidadesDeBind($email) as $identidade) {
            try {
                $connection->connect($identidade, $password);
            } catch (LdapRecordException $e) {
                // Loga o motivo detalhado do AD (ex.: "data 52e" = senha
                // incorreta, "data 525" = usuário não encontrado, "data 533"
                // = conta desabilitada) — o log padrão do LdapRecord só
                // mostra o erro genérico "Invalid credentials".
                Log::warning('Bind direto no AD falhou', [
                    'identidade' => $identidade,
                    'diagnostico' => $e->getDetailedError()?->getDiagnosticMessage(),
                ]);

                continue;
            }

            // O bind confirmou a senha; a mesma conexão (agora autenticada
            // como o próprio usuário) é usada para buscar seus atributos.
            return LdapUser::where('mail', $email)->first();
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function possiveisIdentidadesDeBind(string $email): array
    {
        $usuario = Str::before($email, '@');
        $dominioNetbios = config('ldap.netbios_domain', 'MINERAL');

        return [
            $email,
            "{$dominioNetbios}\\{$usuario}",
        ];
    }
}
