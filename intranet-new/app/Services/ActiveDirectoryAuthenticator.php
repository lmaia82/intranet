<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
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
     * Tenta autenticar e sincronizar o usuário a partir do login e senha
     * informados na tela de login. O login é só a parte antes do "@" (ex:
     * "lgoncalves"), no padrão de rede do CETEM — mas um e-mail completo
     * também é aceito, por compatibilidade. Retorna o usuário local (já
     * salvo) em caso de sucesso, ou null se a senha não confere no AD ou a
     * conta estiver desativada na intranet.
     */
    public function autenticar(string $login, string $password): ?User
    {
        $ldapUser = $this->bindarEBuscarUsuario($login, $password);

        if (! $ldapUser) {
            return null;
        }

        $usuario = $this->sincronizarESalvar($ldapUser);

        // Mesmo com a senha correta no AD, uma conta desativada na intranet
        // (ver Admin > Usuários) não deve conseguir logar — os dados ainda
        // são sincronizados acima, só o acesso é negado.
        if (! $usuario->is_active) {
            return null;
        }

        return $usuario;
    }

    /**
     * O AD do CETEM está em migração e tem objetos duplicados/desatualizados
     * pra uma mesma pessoa (mesmo sAMAccountName, GUIDs diferentes) — o
     * LdapRecord decide se o usuário "já existe" comparando o atributo
     * "mail" bruto do AD (não passa pelo ActiveDirectoryEmailHydrator), que
     * pode estar incompleto/errado num desses objetos duplicados. Nesse
     * caso ele tenta CRIAR um novo usuário local com o e-mail canônico
     * (montado corretamente pelo hydrator) — que já existe, e o save()
     * bate na constraint de unicidade (o synchronizer->run() sozinho só
     * monta os dados em memória, não salva — por isso o try precisa
     * envolver o save() também, não só o run()). Em vez de deixar isso
     * virar erro 500, reaproveita o usuário existente.
     */
    private function sincronizarESalvar(LdapUser $ldapUser): User
    {
        $usuario = $this->synchronizer->run($ldapUser);

        // Só no primeiro login (usuário recém-criado pelo sync acima) — se
        // já existia (ex.: vinculado ao AD por e-mail por já ter sido
        // cadastrado manualmente), setor/grupo continuam como o admin
        // definiu, não são sobrescritos.
        if (! $usuario->exists) {
            $this->provisionarPrimeiroLogin($usuario);
        }

        $usuario->ad_synced_at = now();

        try {
            $usuario->save();
        } catch (UniqueConstraintViolationException $e) {
            $email = ActiveDirectoryEmailHydrator::montar($ldapUser);
            $usuarioExistente = $email ? User::where('email', $email)->first() : null;

            if (! $usuarioExistente) {
                throw $e;
            }

            Log::warning('AD com objeto duplicado para o mesmo usuário — reaproveitando conta existente em vez de criar duplicata', [
                'email' => $email,
                'guid_do_objeto_encontrado_agora' => $ldapUser->getConvertedGuid(),
                'guid_ja_vinculado' => $usuarioExistente->ad_guid,
            ]);

            $usuarioExistente->ad_synced_at = now();
            $usuarioExistente->save();

            return $usuarioExistente;
        }

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

        // O default do banco (true) só é aplicado na linha em si — o modelo
        // em memória fica com is_active = null até ser recarregado, e
        // "! null" seria tratado como desativado. Define explicitamente.
        $usuario->is_active = true;
    }

    /**
     * Tenta o bind direto no AD usando os formatos de identidade aceitos
     * pelo Active Directory, sem precisar de uma conta de serviço: o UPN
     * (montado a partir do login, se só o usuário for informado), e o
     * formato down-level "NETBIOS\usuario" (mesmo padrão usado pela
     * integração já em produção no GLPI do CETEM).
     */
    protected function bindarEBuscarUsuario(string $login, string $password): ?LdapUser
    {
        if (! $this->autenticarConexao($login, $password)) {
            return null;
        }

        // O bind confirmou a senha; a mesma conexão (agora autenticada como
        // o próprio usuário) é usada para buscar seus atributos. Busca por
        // sAMAccountName (o "usuario" de "usuario@dominio"), não por
        // mail — é o identificador de verdade no AD, funciona
        // independente do login ter vindo com ou sem o "@dominio".
        return LdapUser::where('samaccountname', Str::before($login, '@'))->first();
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

        // A importação de um diretório com muitos usuários pode levar mais
        // que os 30s padrão de execução — é uma ação pontual de admin, não
        // uma requisição comum, então vale esperar.
        set_time_limit(300);

        $emailsExistentes = User::query()->pluck('email')
            ->map(fn ($email) => Str::lower($email))
            ->flip()
            ->all();

        $importados = 0;

        // A senha aleatória gravada para cada usuário importado nunca é
        // usada de fato (a senha real é sempre verificada no AD) — hasheá-la
        // no custo padrão do bcrypt (pensado para uma senha real, não para
        // centenas delas em lote) foi o que estourou os 30s no primeiro
        // teste em produção. Reduz o custo só durante este laço.
        $custoOriginal = config('hashing.bcrypt.rounds');
        config(['hashing.bcrypt.rounds' => 4]);

        try {
            foreach ($this->buscarUsuariosAtivos() as $ldapUser) {
                $email = $ldapUser->getFirstAttribute('mail');

                if (! $email || isset($emailsExistentes[Str::lower($email)])) {
                    continue;
                }

                try {
                    $usuario = $this->synchronizer->run($ldapUser);
                    $this->provisionarPrimeiroLogin($usuario);
                    $this->definirDatasDoAd($usuario, $ldapUser);
                    $usuario->ad_synced_at = now();
                    $usuario->save();
                } catch (UniqueConstraintViolationException $e) {
                    // O AD pode ter mais de um objeto com o mesmo "mail"
                    // (dado duplicado/desatualizado no diretório), ou dois
                    // cliques na importação podem correr em paralelo —
                    // nesses casos não travamos a importação inteira, só
                    // pulamos este usuário.
                    Log::warning('Pulando usuário duplicado na importação em lote do AD', [
                        'email' => $email,
                        'erro' => $e->getMessage(),
                    ]);

                    continue;
                }

                $emailsExistentes[Str::lower($email)] = true;
                $importados++;
            }
        } finally {
            config(['hashing.bcrypt.rounds' => $custoOriginal]);
        }

        return $importados;
    }

    /**
     * Aplica no usuário local o "criado em" (whenCreated) e a data de
     * expiração da conta (accountExpires) trazidos do AD — usado na
     * importação em lote. accountexpires vem como 0, o valor máximo do
     * Windows (sentinela "nunca expira") ou uma data real — só uma
     * instância de data representa uma expiração de fato.
     */
    private function definirDatasDoAd(User $usuario, LdapUser $ldapUser): void
    {
        $criadoEmNoAd = $ldapUser->whencreated;
        if ($criadoEmNoAd) {
            $usuario->created_at = $criadoEmNoAd;
        }

        $expiraEmNoAd = $ldapUser->accountexpires;
        $usuario->ad_expira_em = $expiraEmNoAd instanceof \DateTimeInterface ? $expiraEmNoAd : null;
    }

    /**
     * Monta os formatos de identidade que o AD aceita pra bind, a partir do
     * login informado — que pode ser só o usuário ("lgoncalves", padrão de
     * rede do CETEM) ou um e-mail completo ("lgoncalves@cetem.gov.br").
     *
     * @return array<int, string>
     */
    protected function possiveisIdentidadesDeBind(string $login): array
    {
        $usuario = Str::before($login, '@');
        $dominioNetbios = config('ldap.netbios_domain', 'MINERAL');
        $dominioUpn = config('ldap.upn_domain', 'cetem.gov.br');

        // Se já veio com "@" (e-mail completo, ou até de outro domínio),
        // usa como está pro UPN — não força trocar o domínio informado.
        $upn = str_contains($login, '@') ? $login : "{$usuario}@{$dominioUpn}";

        return [
            $upn,
            "{$dominioNetbios}\\{$usuario}",
        ];
    }
}
