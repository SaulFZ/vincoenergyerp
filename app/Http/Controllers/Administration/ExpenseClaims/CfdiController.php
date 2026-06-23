<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseCfdi;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Services\Administration\ExpenseClaims\FiscalConnectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'xml_file' => 'required|file|mimetypes:text/xml,application/xml'
        ]);

        $file = $request->file('xml_file');
        $xmlContent = file_get_contents($file->getRealPath());

        $validation = $this->fiscalService->validateLocalXml($xmlContent);

        if (!$validation['is_valid']) {
            return response()->json(['success' => false, 'message' => 'El archivo XML cargado presenta inconsistencias.'], 422);
        }

        $xml = simplexml_load_string($xmlContent);
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('tfd', $namespaces['tfd']);

        $timbre = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
        $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
        $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;

        if (!$timbre || !$emisor) {
            return response()->json(['success' => false, 'message' => 'No se encontraron los nodos fiscales requeridos en el XML.'], 422);
        }

        $uuid = strtoupper((string) $timbre['UUID']);

        if (ExpenseCfdi::where('uuid', $uuid)->exists()) {
            return response()->json([
                'success' => true,
                'message' => 'Esta factura ya existe en el sistema.',
                'data' => ExpenseCfdi::where('uuid', $uuid)->first()
            ]);
        }

        // ── CORRECCIÓN: Guardar en la bóveda oficial de XMLs ──
        $filename = $uuid . '.xml';
        $path = $file->storeAs('private/administration/expense-claims/xml', $filename);

        $activeNode = FslNode::where('is_live', true)->first();

        $cfdi = ExpenseCfdi::create([
            'fsl_node_id'  => $activeNode ? $activeNode->id : null,
            'uuid'         => $uuid,
            'issuer_rfc'   => (string) $emisor['Rfc'],
            'issuer_name'  => (string) $emisor['Nombre'],
            'receiver_rfc' => $receptor ? (string) $receptor['Rfc'] : null,
            'subtotal'     => (float) $xml['SubTotal'],
            'total'        => (float) $xml['Total'],
            'currency'     => (string) ($xml['Moneda'] ?? 'MXN'),
            'issue_date'   => str_replace('T', ' ', (string) $xml['Fecha']),
            'sat_status'   => 'Vigente',
            'xml_path'     => $path,
            // is_reimbursed se omite porque la BD le pone false por default
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Factura validada y resguardada exitosamente.',
            'data'    => $cfdi
        ]);
    }
}
