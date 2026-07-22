<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LDAP Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the LDAP connections below you wish
    | to use as your default connection for all LDAP operations. Of
    | course you may add as many connections you'd like below.
    |
    */

    'default' => env('LDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Connections
    |--------------------------------------------------------------------------
    |
    | Below you may configure each LDAP connection your application requires
    | access to. Be sure to include a valid base DN - otherwise you may
    | not receive any results when performing LDAP search operations.
    |
    */

    'connections' => [

        // Valores de host/porta/base_dn conferem com a integração LDAP já em
        // produção no GLPI do CETEM (Diretório LDAP > servidor 172.16.0.53,
        // BaseDN OU=CETEM,DC=mineral,DC=cetem).
        //
        // O login da intranet NÃO usa uma conta de serviço fixa (decisão do
        // CETEM): App\Services\ActiveDirectoryAuthenticator autentica cada
        // usuário com o próprio e-mail/senha via bind direto, e reaproveita
        // essa mesma conexão para buscar seus dados. LDAP_USERNAME/PASSWORD
        // abaixo ficam vazios e só serviriam para reativar futuramente o
        // fluxo de importação em lote (`php artisan ldap:import`), que
        // continua exigindo uma conta com acesso de leitura a todo o
        // diretório — sem ela, apenas o próprio login sincroniza os dados
        // de cada usuário.
        'default' => [
            'hosts' => [env('LDAP_HOST', '127.0.0.1')],
            'username' => env('LDAP_USERNAME'),
            'password' => env('LDAP_PASSWORD'),
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN', 'OU=CETEM,DC=mineral,DC=cetem'),
            'timeout' => env('LDAP_TIMEOUT', 5),
            'use_tls' => env('LDAP_TLS', false),
            'use_starttls' => env('LDAP_STARTTLS', false),
            'use_sasl' => env('LDAP_SASL', false),
            'sasl_options' => [
                // 'mech' => 'GSSAPI',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Domínio NetBIOS (bind direto sem conta de serviço)
    |--------------------------------------------------------------------------
    |
    | Usado por App\Services\ActiveDirectoryAuthenticator para montar a
    | identidade de bind no formato "NETBIOS\usuario", igual ao usado pela
    | integração já em produção no GLPI (MINERAL\Administrator).
    |
    */

    'netbios_domain' => env('LDAP_NETBIOS_DOMAIN', 'MINERAL'),

    /*
    |--------------------------------------------------------------------------
    | Sincronização de atributos do usuário local
    |--------------------------------------------------------------------------
    |
    | Usado por App\Services\ActiveDirectoryAuthenticator (via
    | LdapRecord\Laravel\Import\UserSynchronizer) para preencher os campos
    | do usuário local a cada login bem-sucedido no AD. "ad_setor" é bruto
    | (comparação manual na tela de admin) — nunca sobrescreve sector_id.
    |
    */

    'sync_attributes' => [
        'name' => 'cn',
        'email' => 'mail',
        'ad_setor' => 'department',
    ],

    // Vincula pelo e-mail, no primeiro login, usuários já cadastrados
    // manualmente na intranet antes da integração — evita duplicar em vez
    // de criar um segundo registro.
    'sync_existing' => [
        'email' => 'mail',
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Logging
    |--------------------------------------------------------------------------
    |
    | When LDAP logging is enabled, all LDAP search and authentication
    | operations are logged using the default application logging
    | driver. This can assist in debugging issues and more.
    |
    */

    'logging' => [
        'enabled' => env('LDAP_LOGGING', true),
        'channel' => env('LOG_CHANNEL', 'stack'),
        'level' => env('LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Cache
    |--------------------------------------------------------------------------
    |
    | LDAP caching enables the ability of caching search results using the
    | query builder. This is great for running expensive operations that
    | may take many seconds to complete, such as a pagination request.
    |
    */

    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

];
