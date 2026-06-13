<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseCfdi;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Services\Administration\ExpenseClaims\FiscalConnectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CfdiController extends Controller
{
    protected $fiscalService;

    // Inyectamos el servicio mediante el constructor
    public function __construct(FiscalConnectorService $fiscalService)
    {
        $this->fiscalService = $fiscalService;
    }

    /**
     * Recibe un XML arrastrado por el usuario, lo valida y lo guarda.
     */
    public function uploadXml(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimetypes:text/xml,application/xml'
        ]);

        $file = $request->file('xml_file');
        $xmlContent = file_get_contents($file->getRealPath());

        // 1. Validar la estructura del XML usando el Servicio
        $validation = $this->fiscalService->validateLocalXml($xmlContent);

        if (!$validation['is_valid']) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo XML cargado presenta inconsistencias o está corrompido.',
            ], 422);
        }

        // 2. Extraer datos básicos del XML (Parseo manual para DB)
        $xml = simplexml_load_string($xmlContent);
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('tfd', $namespaces['tfd']);

        $timbre = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
        $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
        $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;

        if (!$timbre || !$emisor) {
            return response()->json(['success' => false, 'message' => 'No se encontraron los nodos fiscales requeridos en el XML.'], 422);
        }

        $uuid = (string) $timbre['UUID'];

        // 3. Evitar duplicados (Regla de negocio crítica)
        if (ExpenseCfdi::where('uuid', $uuid)->exists()) {
            return response()->json([
                'success' => true,
                'message' => 'Esta factura ya existe en el sistema.',
                'data' => ExpenseCfdi::where('uuid', $uuid)->first()
            ]);
        }

        // 4. Guardar archivo físico en la bóveda
        // Generamos un nombre seguro para el archivo
        $filename = $uuid . '_' . Str::random(5) . '.xml';
        $path = $file->storeAs('private/administration/expense-claims/invoices', $filename);

        // 5. Guardar en Base de Datos
        $activeNode = FslNode::where('is_live', true)->first();

        $cfdi = ExpenseCfdi::create([
            'fsl_node_id'  => $activeNode ? $activeNode->id : null,
            'uuid'         => $uuid,
            'issuer_rfc'   => (string) $emisor['Rfc'],
            'issuer_name'  => (string) $emisor['Nombre'],
            'receiver_rfc' => (string) $receptor['Rfc'],
            'subtotal'     => (float) $xml['SubTotal'],
            'total'        => (float) $xml['Total'],
            'currency'     => (string) ($xml['Moneda'] ?? 'MXN'),
            'issue_date'   => (string) $xml['Fecha'],
            'sat_status'   => 'Vigente', // Se asume vigente temporalmente hasta la verificación masiva
            'is_reimbursed'=> false,
            'xml_path'     => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Factura validada y resguardada exitosamente.',
            'data'    => $cfdi
        ]);
    }
}
