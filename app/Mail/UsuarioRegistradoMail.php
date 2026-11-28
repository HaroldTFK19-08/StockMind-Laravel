<?php

namespace App\Mail;

use App\Modules\Usuarios\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UsuarioRegistradoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $usuario) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Registro recibido en StockMind');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.usuario-registrado');
    }
}
