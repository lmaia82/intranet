<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Laravel\Events\Import\Saved;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
