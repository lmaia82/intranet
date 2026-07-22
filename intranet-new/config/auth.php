<?php

use App\Models\User;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        // Provider híbrido: a senha é verificada via bind direto no AD para
        // usuários vinculados (ad_guid preenchido); usuários só-intranet
        // (mantidos manualmente para administração, sem conta no AD) caem
        // no fallback local por Eloquent — ver App\Http\Requests\Auth\LoginRequest.
        'users' => [
            'driver' => 'ldap',
            'model' => LdapUser::class,
            'rules' => [],
            'database' => [
                'model' => User::class,
                'sync_passwords' => false,
                'sync_attributes' => [
                    'name' => 'cn',
                    'email' => 'mail',
                    'ad_setor' => 'department',
                ],
                // Vincula por e-mail, na primeira importação, usuários já
                // cadastrados manualmente na intranet antes da integração
                // — evita duplicar em vez de criar um segundo registro.
                'sync_existing' => [
                    'email' => 'mail',
                ],
            ],
        ],

        // Provider Eloquent puro, usado apenas pelo broker de redefinição de
        // senha (abaixo) — a senha de usuários vinculados ao AD é gerenciada
        // lá, não faz sentido reenviar link de redefinição por e-mail para
        // eles; isso continua servindo aos usuários só-intranet.
        'users_local' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users_local',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
