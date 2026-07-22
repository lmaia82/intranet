<?php

use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RegistrarAcesso;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Mantém nome/e-mail/setor(AD) sincronizados com o Active Directory.
        // Filtro conferido com a integração LDAP já em produção no GLPI do
        // CETEM (exclui contas desabilitadas via userAccountControl).
        $schedule->command('ldap:import users', [
            '--no-interaction',
            '--filter=(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
        ])->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'onlyoffice/callback/*',
            'webhooks/paperless',
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'permission' => EnsureUserHasPermission::class,
            'registrar.acesso' => RegistrarAcesso::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
