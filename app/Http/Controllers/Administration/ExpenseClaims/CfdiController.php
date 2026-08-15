<?php
namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseCfdi;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Services\Administration\ExpenseClaims\FiscalConnectorService;
use Illuminate\Http\Request;

class CfdiController extends Controller
{
    protected $fiscalService;

    public function __construct(FiscalConnectorService $fiscalService)
    {
        $this->fiscalService = $fiscalService;
    }

    public function uploadXml(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimetypes:text/xml,application/xml',
        ]);

        $file       = $request->file('xml_file');
        $xmlContent = file_get_contents($file->getRealPath());

        $validation = $this->fiscalService->validateLocalXml($xmlContent);

        if (! $validation['is_valid']) {
            return response()->json(['success' => false, 'message' => 'El archivo XML cargado presenta inconsistencias.'], 422);
        }

        $xml        = simplexml_load_string($xmlContent);
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
        $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
        $xml->registerXPathNamespace('implocal', 'http://www.sat.gob.mx/implocal');
        // ── NUEVO: Agregamos el namespace de Pagos 2.0 ──
        $xml->registerXPathNamespace('pago20', 'http://www.sat.gob.mx/Pagos20');

        $timbre      = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
        $comprobante = $xml->xpath('/cfdi:Comprobante')[0] ?? null;
        $emisor      = $xml->xpath('/cfdi:Comprobante/cfdi:Emisor')[0] ?? null;
        $receptor    = $xml->xpath('/cfdi:Comprobante/cfdi:Receptor')[0] ?? null;

        if (! $timbre || ! $emisor || ! $comprobante) {
            return response()->json(['success' => false, 'message' => 'No se encontraron los nodos fiscales requeridos en el XML.'], 422);
        }

        $uuid = strtoupper((string) $timbre['UUID']);

        if (ExpenseCfdi::where('uuid', $uuid)->exists()) {
            return response()->json([
                'success' => true,
                'message' => 'Esta factura ya existe en el sistema.',
                'data'    => ExpenseCfdi::where('uuid', $uuid)->first(),
            ]);
        }

        $cfdiType = (string) $comprobante['TipoDeComprobante'] ?: null;
        $subtotal = (float) $comprobante['SubTotal'];
        $total    = (float) $comprobante['Total'];

        // ── RESUMEN DE CONCEPTOS ──
        $conceptos = $xml->xpath('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');
        $resumen   = [];
        if ($conceptos) {
            foreach ($conceptos as $index => $concepto) {
                $resumen[] = (string) $concepto['Descripcion'];
                if ($index == 1 && count($conceptos) > 2) {
                    $resumen[] = '... (+ ' . (count($conceptos) - 2) . ' arts. más)';
                    break;
                }
            }
        }
        $conceptSummaryStr = implode(', ', $resumen);

        // ── IMPUESTOS GLOBALES ──
        $iva         = 0.00;
        $retenciones = 0.00;
        $ish         = 0.00;

        $nodosIva = $xml->xpath('/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado[@Impuesto="002"]');
        if ($nodosIva) {
            foreach ($nodosIva as $n) {$iva += (float) $n['Importe'];}
        }

        $nodosRet = $xml->xpath('/cfdi:Comprobante/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion');
        if ($nodosRet) {
            foreach ($nodosRet as $n) {$retenciones += (float) $n['Importe'];}
        }

        $nodoIsh = $xml->xpath('//implocal:ImpuestosLocales');
        if ($nodoIsh && isset($nodoIsh[0]['TotaldeTraslados'])) {
            $ish = (float) $nodoIsh[0]['TotaldeTraslados'];
        }

        // ── REGLA ESPECIAL PARA COMPLEMENTOS DE PAGO (TIPO P) ──
        if ($cfdiType === 'P') {
            $nodoTotalesPago = $xml->xpath('//pago20:Totales')[0] ?? null;
            if ($nodoTotalesPago) {
                $total    = (float) $nodoTotalesPago['MontoTotalPagos'];
                $baseIva  = isset($nodoTotalesPago['TotalTrasladosBaseIVA16']) ? (float) $nodoTotalesPago['TotalTrasladosBaseIVA16'] : 0;
                $subtotal = $baseIva > 0 ? $baseIva : $total;
                $iva      = isset($nodoTotalesPago['TotalTrasladosImpuestoIVA16']) ? (float) $nodoTotalesPago['TotalTrasladosImpuestoIVA16'] : 0;
            }
            $conceptSummaryStr = 'Pago de Factura (Complemento de Recepción de Pagos)';
        }

        $filename = $uuid . '.xml';
        $path     = $file->storeAs('private/administration/expense-claims/xml', $filename);

        $activeNode  = FslNode::where('is_live', true)->first();

        $cfdi = ExpenseCfdi::create([
            'fsl_node_id'     => $activeNode ? $activeNode->id : null,
            'uuid'            => $uuid,
            'serie'           => (string) $comprobante['Serie'] ?: null,
            'folio'           => (string) $comprobante['Folio'] ?: null,
            'cfdi_type'       => $cfdiType,
            'payment_method'  => (string) $comprobante['MetodoPago'] ?: null,
            'payment_form'    => (string) $comprobante['FormaPago'] ?: null,
            'use_cfdi'        => $receptor ? (string) $receptor['UsoCFDI'] : null,
            'concept_summary' => $conceptSummaryStr,
            'issuer_rfc'      => (string) $emisor['Rfc'],
            'issuer_name'     => (string) $emisor['Nombre'],
            'receiver_rfc'    => $receptor ? (string) $receptor['Rfc'] : null,
            'receiver_name'   => $receptor ? (string) $receptor['Nombre'] : null,
            'subtotal'        => $subtotal,
            'total'           => $total,
            'tax_iva'         => $iva,
            'tax_ish'         => $ish,
            'tax_retenciones' => $retenciones,
            'currency'        => (string) ($comprobante['Moneda'] ?? 'MXN'),
            'issue_date'      => str_replace('T', ' ', (string) $comprobante['Fecha']),
            'sat_status'      => 'Vigente',
            'xml_path'        => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Factura validada y resguardada exitosamente.',
            'data'    => $cfdi,
        ]);
    }

    public function autocomplete(Request $request)
    {
        // Acepta tanto 'term' como 'q' para evitar errores de compatibilidad
        $queryParam = $request->query('term') ?? $request->query('q', '');
        $term = strtoupper(trim($queryParam));

        if (strlen($term) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Búsqueda inteligente: UUID parcial, Folio, Serie o Razón Social
        $results = ExpenseCfdi::where('uuid', 'like', "%{$term}%")
            ->orWhere('folio', 'like', "%{$term}%")
            ->orWhere('serie', 'like', "%{$term}%")
            ->orWhere('issuer_name', 'like', "%{$term}%")
            ->select('uuid', 'serie', 'folio', 'issuer_name', 'total', 'concept_summary', 'subtotal')
            ->limit(10)
            ->get();

        // DEBEMOS devolverlo con 'success' y 'data' para que el JS lo entienda
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

}
