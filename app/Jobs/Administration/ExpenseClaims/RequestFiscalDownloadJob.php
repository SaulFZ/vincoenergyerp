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

    public function __construct(string $rfc, string $start, string $end, int $satRequestId)
    {
        $this->rfc = $rfc;
        $this->start = $start;
        $this->end = $end;
        $this->satRequestId = $satRequestId;
    }

    public function handle(FiscalConnectorService $service): void
    {
        Log::info("Job 1: Iniciando petición masiva al SAT para el RFC: {$this->rfc}");

        try {
            $node = FslNode::where('is_live', true)->first();

            if (!$node) {
                throw new \Exception("No existe un certificado activo configurado en el sistema.");
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
                    Log::info("Job 1: Ticket oficial de SAT [{$ticketId}] guardado en la solicitud ID: {$this->satRequestId}");
                }
            } else {
                $this->markAsFailed();
            }

        } catch (\Exception $e) {
            Log::error("Job 1: Fallo al solicitar descarga masiva: " . $e->getMessage());
            $this->markAsFailed();
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
