<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Laravel\Events\Import\Saved;
use LdapRecord\Laravel\Import\UserSynchronizer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

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

        $this->desabilitarVerificacaoTlsSmtpSeNecessario();
    }

    /**
     * Workaround temporário: o certificado TLS de mail.cetem.gov.br (relay
     * SMTP do CETEM) está vencido. Enquanto a infra não renova, defina
     * MAIL_VERIFY_PEER=false para permitir o envio mesmo assim (o handshake
     * STARTTLS não valida o certificado — reverter assim que for renovado).
     */
    private function desabilitarVerificacaoTlsSmtpSeNecessario(): void
    {
        if (config('mail.default') !== 'smtp' || config('mail.mailers.smtp.verify_peer', true)) {
            return;
        }

        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        if (! $transport instanceof EsmtpTransport || ! $transport->getStream() instanceof SocketStream) {
            return;
        }

        $stream = $transport->getStream();

        $stream->setStreamOptions(array_replace_recursive($stream->getStreamOptions(), [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
        ]));
    }
}
