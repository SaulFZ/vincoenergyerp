<?php

namespace App\Mail\System\Tickets;

use App\Models\Systems\Tickets\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class NewTicketAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;

    /**
     * Inyectamos el modelo Ticket al instanciar el correo
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Construye el mensaje y apunta a la vista correspondiente
     */
    public function build()
    {
        return $this->subject('Nuevo Ticket en Cola: ' . $this->ticket->folio)
                    ->view('emails.system.tickets.new_ticket');
    }
}
