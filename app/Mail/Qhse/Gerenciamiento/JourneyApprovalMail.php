<?php

namespace App\Mail\Qhse\Gerenciamiento;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Qhse\Gerenciamiento\Journey;

class JourneyApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $journey;

    public function __construct(Journey $journey)
    {
        $this->journey = $journey;
    }

    public function build()
    {
        return $this->subject('Solicitud de Viaje: ' . $this->journey->folio)
                    // Ruta en minúsculas hacia resources/views/emails/...
                    ->view('emails.qhse.gerenciamiento.journey_approval');
    }
}
