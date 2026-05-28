<?php

namespace App\Mail\Qhse\Management;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Qhse\Management\Journey;

class AnomalyNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $journey;
    public $anomaliesList;

    public function __construct(Journey $journey, array $anomaliesList)
    {
        $this->journey = $journey;
        $this->anomaliesList = $anomaliesList;
    }

    public function build()
    {
        return $this->subject('⚠️ ALERTA: Anomalía detectada en Viaje ' . $this->journey->folio)
                    ->view('emails.qhse.management.anomaly_notification');
    }
}
