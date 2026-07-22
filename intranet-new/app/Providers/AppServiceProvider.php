<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Laravel\Events\Import\Saved;
use LdapRecord\Laravel\Import\UserSynchronizer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Usado por App\Services\ActiveDirectoryAuthenticator para
        // sincronizar nome/e-mail/setor após um bind direto no AD.
        $this->app->bind(UserSynchronizer::class, function () {
            return new UserSynchronizer(User::class, [
                'sync_passwords' => false,
                'sync_attributes' => config('ldap.sync_attributes'),
                'sync_existing' => config('ldap.sync_existing'),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Marca quando o usuário foi sincronizado pela última vez com o AD,
        // seja por login (bind direto) ou por `php artisan ldap:import`.
        Event::listen(Saved::class, function (Saved $event) {
            $event->eloquent->forceFill(['ad_synced_at' => now()])->saveQuietly();
        });
    }
}
