<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LdapRecord\Container;
use LdapRecord\LdapRecordException;
use LdapRecord\Laravel\Import\UserSynchronizer;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

/**
 * Autentica diretamente no AD (bind), sem depender de uma conta de serviço
 * fixa — o CETEM optou por não cadastrar uma conta de serviço no .env.
 * Login: cada usuário se autentica com a própria credencial. Importação em
 * lote (`importarUsuariosAtivos`): usa a senha do admin que clicou no
 * botão, digitada na hora e nunca armazenada — só autoriza aquela busca.
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

        // Só no primeiro login (usuário recém-criado pelo sync acima) — se
        // já existia (ex.: vinculado ao AD por e-mail por já ter sido
        // cadastrado manualmente), setor/grupo continuam como o admin
        // definiu, não são sobrescritos.
        if (! $usuario->exists) {
            $this->provisionarPrimeiroLogin($usuario);
        }

        $usuario->ad_synced_at = now();
        $usuario->save();

        return $usuario;
    }

    /**
     * Garante o mínimo privilégio por padrão: importa o setor do AD para a
     * intranet automaticamente (quando a sigla corresponde a um setor já
     * cadastrado) e entra no grupo "Leitores" (somente visualização) até um
     * admin decidir elevar o acesso.
     */
    private function provisionarPrimeiroLogin(User $usuario): void
    {
        if ($usuario->ad_setor) {
            $usuario->sector_id = Sector::where('sigla', $usuario->ad_setor)->value('id');
        }

        $usuario->group_id = Group::where('name', 'Leitores')->value('id');
    }

    /**
     * Tenta o bind direto no AD usando os formatos de identidade aceitos
     * pelo Active Directory, sem precisar de uma conta de serviço:
     * o e-mail como UPN, e o formato down-level "NETBIOS\usuario" (mesmo
     * padrão usado pela integração já em produção no GLPI do CETEM).
     */
    protected function bindarEBuscarUsuario(string $email, string $password): ?LdapUser
    {
        if (! $this->autenticarConexao($email, $password)) {
            return null;
        }

        // O bind confirmou a senha; a mesma conexão (agora autenticada como
        // o próprio usuário) é usada para buscar seus atributos.
        return LdapUser::where('mail', $email)->first();
    }

    /**
     * Autentica a conexão LDAP compartilhada com o e-mail/senha informados,
     * sem buscar nenhum usuário — usado tanto para o login quanto para
     * autorizar uma busca em lote no diretório (ver `buscarUsuariosAtivos`).
     */
    public function autenticarConexao(string $email, string $password): bool
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

            return true;
        }

        return false;
    }

    /**
     * Busca todos os usuários ativos do AD (mesmo filtro já usado em
     * produção pelo GLPI: exclui contas desabilitadas). Só funciona com a
     * conexão já autenticada por `autenticarConexao` — sem conta de
     * serviço, uma busca anônima não tem permissão (AD retorna
     * "Operations error").
     *
     * @return \Illuminate\Support\Collection<int, LdapUser>
     */
    public function buscarUsuariosAtivos()
    {
        return LdapUser::rawFilter('(!(userAccountControl:1.2.840.113556.1.4.803:=2))')->get();
    }

    /**
     * Importa para a intranet todos os usuários ativos do AD que ainda não
     * existem localmente (por e-mail), já com setor importado e no grupo
     * "Leitores" (mínimo privilégio) — igual ao provisionamento automático
     * do primeiro login, mas em lote.
     *
     * @return int|null Quantidade de usuários importados, ou null se a
     *                   senha do admin não confere no AD.
     */
    public function importarUsuariosAtivos(string $emailAdmin, string $senhaAdmin): ?int
    {
        if (! $this->autenticarConexao($emailAdmin, $senhaAdmin)) {
            return null;
        }

        $emailsExistentes = User::query()->pluck('email')
            ->map(fn ($email) => Str::lower($email))
            ->flip();

        $importados = 0;

        foreach ($this->buscarUsuariosAtivos() as $ldapUser) {
            $email = $ldapUser->getFirstAttribute('mail');

            if (! $email || $emailsExistentes->has(Str::lower($email))) {
                continue;
            }

            $usuario = $this->synchronizer->run($ldapUser);
            $this->provisionarPrimeiroLogin($usuario);
            $usuario->ad_synced_at = now();
            $usuario->save();

            $importados++;
        }

        return $importados;
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
