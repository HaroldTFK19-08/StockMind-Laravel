<?php

namespace App\Mail;

use App\Modules\Usuarios\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccesoAprobadoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public User $usuario,
        public array $roles,
        public string $codigo
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Acceso aprobado en StockMind');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.acceso-aprobado');
    }
}
