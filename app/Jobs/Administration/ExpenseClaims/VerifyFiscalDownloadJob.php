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

            $statusRequest = $verify->getStatusRequest();
            $codigoGeneral = $verify->getStatus()->getCode();

            if ($statusRequest->isFinished()) {
                Log::info("Job 2: El SAT terminó el procesamiento. Descargando paquetes ZIP.");

                foreach ($verify->getPackagesIds() as $packageId) {
                    $zipPath = $service->downloadPackage($packageId, $node);
                    if ($zipPath) {
                        $this->processZip($zipPath);
                    }
                }

                $satRequest->update(['status' => 'completed']);
                Log::info("Job 2: Descarga masiva finalizada con éxito.");

            } elseif ($statusRequest->isAccepted() || $statusRequest->isInProgress()) {
                Log::info("Job 2: El ticket {$this->ticketId} sigue en proceso en el SAT. Esperaremos al próximo ciclo.");
            } else {
                $satRequest->update(['status' => 'failed']);
                Log::error("Job 2: El paquete falló o fue rechazado (Código SOAP: {$codigoGeneral}).");
            }

        } catch (\Throwable $e) {
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

            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
        }
    }

    private function saveCfdi(string $xmlContent, ?int $nodeId): void
    {
        try {
            $dom = new \DOMDocument();
            $oldEntityLoader = libxml_disable_entity_loader(true);
            $dom->loadXML($xmlContent);
            libxml_disable_entity_loader($oldEntityLoader);

            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
            $xpath->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
            $xpath->registerNamespace('implocal', 'http://www.sat.gob.mx/implocal');
            // ── NUEVO: Agregamos el namespace de Pagos 2.0 ──
            $xpath->registerNamespace('pago20', 'http://www.sat.gob.mx/Pagos20');

            $uuidElement = $xpath->query('//tfd:TimbreFiscalDigital')->item(0);
            if (!$uuidElement) return;

            $uuid = strtoupper($uuidElement->getAttribute('UUID'));
            $comprobante = $xpath->query('/cfdi:Comprobante')->item(0);
            $emisor = $xpath->query('/cfdi:Comprobante/cfdi:Emisor')->item(0);
            $receptor = $xpath->query('/cfdi:Comprobante/cfdi:Receptor')->item(0);

            if (!$comprobante || !$emisor) return;

            $cfdiType = $comprobante->getAttribute('TipoDeComprobante') ?: null;
            $subtotal = (float) $comprobante->getAttribute('SubTotal');
            $total    = (float) $comprobante->getAttribute('Total');

            // ── EXTRACCIÓN DE CONCEPTOS MULTIPLES ──
            $conceptos = $xpath->query('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');
            $resumen = [];
            for ($i = 0; $i < $conceptos->length; $i++) {
                $resumen[] = $conceptos->item($i)->getAttribute('Descripcion');
                if ($i == 1 && $conceptos->length > 2) {
                    $resumen[] = '... (+ ' . ($conceptos->length - 2) . ' arts. más)';
                    break;
                }
            }
            $conceptSummaryStr = implode(', ', $resumen);

            // ── EXTRACCIÓN DE IMPUESTOS ──
            $iva = 0.00;
            $retenciones = 0.00;
            $ish = 0.00;

            $nodosIva = $xpath->query('/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado[@Impuesto="002"]');
            foreach ($nodosIva as $nodo) { $iva += (float) $nodo->getAttribute('Importe'); }

            $nodosRet = $xpath->query('/cfdi:Comprobante/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion');
            foreach ($nodosRet as $nodo) { $retenciones += (float) $nodo->getAttribute('Importe'); }

            $nodoIsh = $xpath->query('//implocal:ImpuestosLocales')->item(0);
            if ($nodoIsh) { $ish = (float) $nodoIsh->getAttribute('TotaldeTraslados'); }

            // ── REGLA ESPECIAL PARA COMPLEMENTOS DE PAGO (TIPO P) ──
            if ($cfdiType === 'P') {
                $nodoTotalesPago = $xpath->query('//pago20:Totales')->item(0);
                if ($nodoTotalesPago) {
                    $total = (float) $nodoTotalesPago->getAttribute('MontoTotalPagos');
                    $baseIva = (float) $nodoTotalesPago->getAttribute('TotalTrasladosBaseIVA16');
                    $subtotal = $baseIva > 0 ? $baseIva : $total;
                    $iva = (float) $nodoTotalesPago->getAttribute('TotalTrasladosImpuestoIVA16');
                }
                $conceptSummaryStr = 'Pago de Factura (Complemento de Recepción de Pagos)';
            }

            $finalXmlFolder = 'private/administration/expense-claims/xml';
            $finalXmlPath = $finalXmlFolder . '/' . $uuid . '.xml';

            if (!Storage::exists($finalXmlPath)) {
                Storage::makeDirectory($finalXmlFolder);
                Storage::put($finalXmlPath, $xmlContent);
            }

            ExpenseCfdi::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'fsl_node_id'    => $nodeId,
                    'serie'          => $comprobante->getAttribute('Serie') ?: null,
                    'folio'          => $comprobante->getAttribute('Folio') ?: null,
                    'cfdi_type'      => $cfdiType,
                    'payment_method' => $comprobante->getAttribute('MetodoPago') ?: null,
                    'payment_form'   => $comprobante->getAttribute('FormaPago') ?: null,
                    'use_cfdi'       => $receptor ? $receptor->getAttribute('UsoCFDI') : null,
                    'concept_summary'=> $conceptSummaryStr,
                    'issuer_rfc'     => (string) $emisor->getAttribute('Rfc'),
                    'issuer_name'    => (string) $emisor->getAttribute('Nombre'),
                    'receiver_rfc'   => $receptor ? (string) $receptor->getAttribute('Rfc') : null,
                    'receiver_name'  => $receptor ? (string) $receptor->getAttribute('Nombre') : null,
                    'subtotal'       => $subtotal,
                    'total'          => $total,
                    'tax_iva'        => $iva,
                    'tax_ish'        => $ish,
                    'tax_retenciones'=> $retenciones,
                    'currency'       => (string) ($comprobante->getAttribute('Moneda') ?: 'MXN'),
                    'issue_date'     => str_replace('T', ' ', $comprobante->getAttribute('Fecha')),
                    'sat_status'     => 'Vigente',
                    'xml_path'       => $finalXmlPath,
                ]
            );

        } catch (\Exception $e) {
            Log::error("Error inyectando XML desde Job 2: " . $e->getMessage());
        }
    }
}
