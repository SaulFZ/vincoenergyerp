<?php

namespace App\Jobs\Administration\ExpenseClaims;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\Administration\ExpenseClaims\FiscalConnectorService;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;

class RequestFiscalDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rfc;
    protected $start;
    protected $end;
    protected $satRequestId;

    // ── LÓGICA DE RESILIENCIA (ANTI-CAÍDAS DEL SAT) ──
    public $tries = 3; // Intentará 3 veces antes de fallar definitivamente
    public $backoff = [60, 300, 600]; // Esperará 1 min, luego 5 min, luego 10 min entre intentos

    public function __construct(string $rfc, string $start, string $end, int $satRequestId)
    {
        $this->rfc = $rfc;
        $this->start = $start;
        $this->end = $end;
        $this->satRequestId = $satRequestId;
    }

    public function handle(FiscalConnectorService $service): void
    {
        Log::info("Job 1: Iniciando petición masiva al SAT para el RFC: {$this->rfc} (Intento: " . $this->attempts() . ")");

        try {
            $node = FslNode::where('is_live', true)->first();

            if (!$node) {
                // Este error es nuestro, no del SAT. Lo fallamos de inmediato sin reintentos.
                $this->markAsFailed();
                Log::error("Job 1: No hay certificado activo configurado.");
                return;
            }

            $ticketId = $service->requestDownload(
                $this->rfc,
                new \DateTimeImmutable($this->start),
                new \DateTimeImmutable($this->end),
                $node
            );

            if ($ticketId) {
                $satRequest = SatRequest::find($this->satRequestId);
                if ($satRequest) {
                    $satRequest->update([
                        'ticket_id' => $ticketId
                    ]);
                    Log::info("Job 1: Ticket oficial de SAT [{$ticketId}] guardado con éxito.");
                }
            } else {
                throw new \Exception("El SAT no devolvió un Ticket ID válido.");
            }

        } catch (\Exception $e) {
            Log::warning("Job 1: SAT falló en el intento " . $this->attempts() . ". Error: " . $e->getMessage());

            // Si ya llegamos al límite de intentos, lo marcamos como fallido en la BD
            if ($this->attempts() >= $this->tries) {
                Log::error("Job 1: Se agotaron los intentos. El SAT sigue rechazando la conexión.");
                $this->markAsFailed();
            }

            // Volvemos a lanzar el error para que la cola de Laravel aplique el $backoff (reintento)
            throw $e;
        }
    }

    private function markAsFailed(): void
    {
        $satRequest = SatRequest::find($this->satRequestId);
        if ($satRequest) {
            $satRequest->update(['status' => 'failed']);
        }
    }
}
