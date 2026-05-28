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
    public $hasAnomalies; // Nueva variable para la vista

    public function __construct(Journey $journey)
    {
        $this->journey = $journey;

        // Cargamos las relaciones de las unidades y sus inspecciones para poder leerlas
        $this->journey->loadMissing(['units.lightInspection', 'units.heavyInspection']);

        // Lógica para detectar si alguna unidad reportó anomalías
        $this->hasAnomalies = false;
        foreach ($this->journey->units as $unit) {
            if (($unit->lightInspection && $unit->lightInspection->has_anomalies) ||
                ($unit->heavyInspection && $unit->heavyInspection->has_anomalies)) {
                $this->hasAnomalies = true;
                break; // Si encontramos al menos una anomalía, detenemos la búsqueda
            }
        }
    }

    public function build()
    {
        return $this->subject('Solicitud de Viaje: ' . $this->journey->folio)
                    // Ruta en minúsculas hacia resources/views/emails/...
                    ->view('emails.qhse.management.journey_approval');
    }
}
