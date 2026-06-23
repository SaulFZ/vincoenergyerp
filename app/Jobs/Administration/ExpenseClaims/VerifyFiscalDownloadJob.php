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
use App\Models\Administration\ExpenseClaims\FslNode; // IMPORTANTE: Agregado para el nodo
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
        Log::info("Job 2: Validando estatus del ticket SAT: {$this->ticketId}");

        try {
            $verify = $service->verifyDownload($this->ticketId);

            if (!$verify) {
                Log::warning("Job 2: Sin respuesta del SAT para el ticket {$this->ticketId}. Se volverá a intentar en la siguiente ejecución programada.");
                return;
            }

            // Validamos el código de estado del paquete del SAT
            // '5000' significa: El SAT terminó de empaquetar y está listo para descarga.
            if ($verify->getCodeRequest()->getValue() === '5000') {

                Log::info("Job 2: El SAT aprobó y empaquetó la solicitud. Iniciando descargas de paquetes ZIP.");

                foreach ($verify->getPackageIds() as $packageId) {
                    $zipPath = $service->downloadPackage($packageId);
                    if ($zipPath) {
                        $this->processZip($zipPath);
                    }
                }

                // ACTUALIZACIÓN DE ESTADO: Cambiamos la solicitud a completada
                $satRequest = SatRequest::find($this->satRequestId);
                if ($satRequest) {
                    $satRequest->update(['status' => 'completed']);
                    Log::info("Job 2: Descarga diaria finalizada. Estatus de Solicitud ID {$this->satRequestId} actualizado a COMPLETED. Preguntas suspendidas.");
                }

            } else {
                // Si el código no es 5000, significa que el SAT lo sigue procesando en sus servidores.
                // No hacemos nada; la tarea programada de la consola volverá a lanzarlo en 2 horas automáticamente.
                Log::info("Job 2: El SAT sigue procesando el ticket {$this->ticketId}. Código actual: " . $verify->getCodeRequest()->getValue());
            }

        } catch (\Exception $e) {
            Log::error("Job 2: Error crítico en la verificación de descargas: " . $e->getMessage());
        }
    }

    private function processZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {

            // Obtenemos el nodo activo una sola vez antes del loop para optimizar rendimiento
            $activeNode = FslNode::where('is_live', true)->first();
            $nodeId = $activeNode ? $activeNode->id : null;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (pathinfo($filename, PATHINFO_EXTENSION) === 'xml') {
                    $xmlContent = $zip->getFromIndex($i);
                    $this->saveCfdi($xmlContent, $nodeId); // Pasamos el ID del nodo
                }
            }
            $zip->close();

            // Destrucción física del paquete ZIP (Estrategia de limpieza)
            Storage::delete('private/temp_sat/' . basename($zipPath));
        }
    }

  /**
     * Procesa el contenido crudo de un XML, valida duplicados en BD y
     * almacena el archivo físico en la carpeta definitiva de manera eficiente.
     */
    private function saveCfdi(string $xmlContent, ?int $nodeId): void
    {
        try {
            // 1. Parseamos el XML usando el motor nativo de PHP
            $dom = new \DOMDocument();
            // Desactivamos la carga de entidades externas por seguridad (Prevención de ataques XXE)
            $oldEntityLoader = libxml_disable_entity_loader(true);
            $dom->loadXML($xmlContent);
            libxml_disable_entity_loader($oldEntityLoader);

            // 2. Extraemos el UUID (Folio Fiscal) del nodo del Timbre Fiscal Digital
            $uuidElement = $dom->getElementsByTagNameNS('*', 'TimbreFiscalDigital')->item(0);
            if (!$uuidElement) {
                return; // Si no tiene Timbre Fiscal, no es un CFDI válido del SAT
            }

            $uuid = strtoupper($uuidElement->getAttribute('UUID'));

            // 3. Extraemos los nodos principales para la recolecta de datos fiscales
            $comprobante = $dom->getElementsByTagNameNS('*', 'Comprobante')->item(0);
            $emisor = $dom->getElementsByTagNameNS('*', 'Emisor')->item(0);
            $receptor = $dom->getElementsByTagNameNS('*', 'Receptor')->item(0);

            if (!$comprobante || !$emisor) {
                return;
            }

            // ── OPTIMIZACIÓN DE DISCO (EVITAR SOBREESCRITURA INÚTIL) ──
            $finalXmlFolder = 'private/administration/expense-claims/xml';
            $finalXmlPath = $finalXmlFolder . '/' . $uuid . '.xml';

            if (!Storage::exists($finalXmlPath)) {
                Storage::ensureDirectoryExists($finalXmlFolder);
                Storage::put($finalXmlPath, $xmlContent);
            }

            // ── OPTIMIZACIÓN DE BASE DE DATOS (UPSERT LIBRE DE DUPLICADOS) ──
            ExpenseCfdi::updateOrCreate(
                [
                    'uuid' => $uuid
                ],
                [
                    'fsl_node_id'   => $nodeId, // Relacionamos con el certificado que lo descargó
                    'subtotal'      => (string) $comprobante->getAttribute('SubTotal'),
                    'total'         => (string) $comprobante->getAttribute('Total'),
                    'issuer_rfc'    => (string) $emisor->getAttribute('Rfc'),
                    'issuer_name'   => (string) $emisor->getAttribute('Nombre'),
                    'receiver_rfc'  => $receptor ? (string) $receptor->getAttribute('Rfc') : null,
                    'currency'      => (string) ($comprobante->getAttribute('Moneda') ?? 'MXN'),
                    'issue_date'    => str_replace('T', ' ', $comprobante->getAttribute('Fecha')),
                    'sat_status'    => 'Vigente',
                    'xml_path'      => $finalXmlPath,
                    // is_reimbursed no se incluye aquí para que no sobreescriba a false
                    // si la factura ya había sido procesada en un reembolso.
                ]
            );

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error procesando e inyectando XML del SAT (UUID: " . ($uuid ?? 'Desconocido') . "): " . $e->getMessage());
        }
    }
}
