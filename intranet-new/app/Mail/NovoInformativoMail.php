<?php

namespace App\Mail;

use App\Models\Informativo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovoInformativoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Informativo $informativo)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo informativo: ' . $this->informativo->title,
            replyTo: [config('mail.no_reply_address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.informativo',
            with: ['informativo' => $this->informativo],
        );
    }
}
