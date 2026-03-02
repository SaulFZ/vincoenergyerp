<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $expirationMinutes; // Propiedad para el tiempo de expiración
    public $userName; // Nueva propiedad para el nombre del usuario

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $expirationMinutes = 5, $userName = 'Usuario')
    {
        $this->token = $token;
        $this->expirationMinutes = $expirationMinutes;
        $this->userName = $userName; // Asignar el nombre
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Código de Verificación para Restablecer Contraseña',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.login.password_reset',
            // El token, la expiración y el nombre de usuario se pasan automáticamente a la vista.
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
