<?php

namespace App\Mail;

use App\Models\Convite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ConviteUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Convite $convite,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        // tenta vir do config (aplicado em runtime pela tela de Integração de E-mail)
        $fromAddress = config('mail.from.address');
        $fromName    = config('mail.from.name') ?: config('app.name', 'Marokah');

        // fallback: lê direto da tabela settings, se por algum motivo o config não estiver setado
        if (blank($fromAddress)) {
            $payload = DB::table('settings')
                ->where('group', 'email')->where('name', 'default')
                ->value('payload');

            if ($payload) {
                $p = json_decode($payload, true) ?: [];
                $fromAddress = $p['from_address'] ?? null;
                $fromName    = $p['from_name']    ?? $fromName;
            }
        }

        return new Envelope(
            from: $fromAddress ? new Address($fromAddress, $fromName) : null,
            subject: 'Convite para criar sua conta',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.convites.convite',
            with: [
                'convite' => $this->convite,
                'url'     => $this->url,
            ],
        );
    }
}
