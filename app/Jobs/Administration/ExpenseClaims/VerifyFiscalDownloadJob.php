<?php

namespace App\Jobs\Administration\ExpenseClaims;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\Administration\ExpenseClaims\FiscalConnectorService;
use App\Models\Administration\ExpenseClaims\ExpenseCfdi;
use App\Models\Administration\ExpenseClaims\SatRequest;
use App\Models\Administration\ExpenseClaims\FslNode;
use ZipArchive;

class VerifyFiscalDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticketId;
    protected $satRequestId;

    public function __construct(string $ticketId, int $satRequestId)
    {
        $this->ticketId = $ticketId;
        $this->satRequestId = $satRequestId;
    }

    public function handle(FiscalConnectorService $service): void
    {
        Log::info("Job 2: Validando estatus del ticket oficial del SAT: {$this->ticketId}");

        try {
            $node = FslNode::where('is_live', true)->first();
            $satRequest = SatRequest::find($this->satRequestId);

            if (!$node || !$satRequest) {
                Log::warning("Job 2: Credenciales o registro de solicitud no localizados.");
                return;
            }

            $verify = $service->verifyDownload($this->ticketId, $node);

            if (!$verify) {
                Log::warning("Job 2: Sin respuesta del servidor SOAP del SAT para el ticket {$this->ticketId}.");
                return;
            }

            $statusCode = $verify->getCodeRequest()->getValue();

            if ($statusCode === '5000') {
                Log::info("Job 2: El SAT terminó el procesamiento. Descargando paquetes ZIP.");

                foreach ($verify->getPackageIds() as $packageId) {
                    $zipPath = $service->downloadPackage($packageId, $node);
                    if ($zipPath) {
                        $this->processZip($zipPath);
                    }
                }

                $satRequest->update(['status' => 'completed']);
                Log::info("Job 2: Descarga masiva finalizada con éxito.");

            } elseif ($statusCode === '5001' || $statusCode === '5002') {
                Log::info("Job 2: El SAT reporta que el ticket {$this->ticketId} sigue en proceso (Código: {$statusCode}).");
            } else {
                $satRequest->update(['status' => 'failed']);
                Log::error("Job 2: El SAT rechazó el ticket {$this->ticketId} (Código: {$statusCode}).");
            }

        } catch (\Exception $e) {
            Log::error("Job 2: Error en verificación de descargas: " . $e->getMessage());
        }
    }

    private function processZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $activeNode = FslNode::where('is_live', true)->first();
            $nodeId = $activeNode ? $activeNode->id : null;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (pathinfo($filename, PATHINFO_EXTENSION) === 'xml') {
                    $xmlContent = $zip->getFromIndex($i);
                    $this->saveCfdi($xmlContent, $nodeId);
                }
            }
            $zip->close();
            Storage::delete('private/temp_sat/' . basename($zipPath));
        }
    }

    private function saveCfdi(string $xmlContent, ?int $nodeId): void
    {
        try {
            $dom = new \DOMDocument();
            $oldEntityLoader = libxml_disable_entity_loader(true);
            $dom->loadXML($xmlContent);
            libxml_disable_entity_loader($oldEntityLoader);

            $uuidElement = $dom->getElementsByTagNameNS('*', 'TimbreFiscalDigital')->item(0);
            if (!$uuidElement) return;

            $uuid = strtoupper($uuidElement->getAttribute('UUID'));
            $comprobante = $dom->getElementsByTagNameNS('*', 'Comprobante')->item(0);
            $emisor = $dom->getElementsByTagNameNS('*', 'Emisor')->item(0);
            $receptor = $dom->getElementsByTagNameNS('*', 'Receptor')->item(0);

            if (!$comprobante || !$emisor) return;

            $finalXmlFolder = 'private/administration/expense-claims/xml';
            $finalXmlPath = $finalXmlFolder . '/' . $uuid . '.xml';

            if (!Storage::exists($finalXmlPath)) {
                Storage::ensureDirectoryExists($finalXmlFolder);
                Storage::put($finalXmlPath, $xmlContent);
            }

            ExpenseCfdi::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'fsl_node_id'   => $nodeId,
                    'subtotal'      => (string) $comprobante->getAttribute('SubTotal'),
                    'total'         => (string) $comprobante->getAttribute('Total'),
                    'issuer_rfc'    => (string) $emisor->getAttribute('Rfc'),
                    'issuer_name'   => (string) $emisor->getAttribute('Nombre'),
                    'receiver_rfc'  => $receptor ? (string) $receptor->getAttribute('Rfc') : null,
                    'currency'      => (string) ($comprobante->getAttribute('Moneda') ?? 'MXN'),
                    'issue_date'    => str_replace('T', ' ', $comprobante->getAttribute('Fecha')),
                    'sat_status'    => 'Vigente',
                    'xml_path'      => $finalXmlPath,
                ]
            );

        } catch (\Exception $e) {
            Log::error("Error inyectando XML desde Job 2: " . $e->getMessage());
        }
    }
}
