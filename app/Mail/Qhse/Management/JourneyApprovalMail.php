<?php

namespace App\Mail\Qhse\Management;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Qhse\Management\Journey;

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
                    ->view('emails.qhse.management.journey_approval');
    }
}
