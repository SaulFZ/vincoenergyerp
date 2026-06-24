<?php

namespace App\Services\Administration\ExpenseClaims;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime as SatDateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;
use App\Models\Administration\ExpenseClaims\FslNode;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
class FiscalConnectorService
{
    // ─── Métodos privados reutilizables ───────────────────────────────────────

    /**
     * Construye el objeto Fiel validando que el nodo tenga
     * los archivos y la contraseña correctamente configurados.
     *
     * Columnas reales del modelo FslNode:
     *   - c_bin     → ruta del certificado .cer
     *   - k_bin     → ruta de la llave privada .key
     *   - sec_token → contraseña encriptada con Crypt::encryptString()
     *
     * @throws \RuntimeException si faltan datos o los archivos no existen en storage.
     */
    private function buildFiel(FslNode $node): Fiel
    {
        if (blank($node->c_bin) || blank($node->k_bin) || blank($node->sec_token)) {
            throw new \RuntimeException(
                "El nodo FSL (ID: {$node->id}) no tiene configurado el certificado (.cer), " .
                "la llave privada (.key) o la contraseña. Revisa el registro en la base de datos."
            );
        }

        if (!Storage::exists($node->c_bin)) {
            throw new \RuntimeException(
                "Archivo .cer no encontrado en storage: '{$node->c_bin}' (Nodo ID: {$node->id})"
            );
        }

        if (!Storage::exists($node->k_bin)) {
            throw new \RuntimeException(
                "Archivo .key no encontrado en storage: '{$node->k_bin}' (Nodo ID: {$node->id})"
            );
        }

        $password = Crypt::decryptString($node->sec_token);

        return Fiel::create(
            Storage::get($node->c_bin),
            Storage::get($node->k_bin),
            $password
        );
    }

    /**
     * Construye un Service de phpcfdi listo para consumir el WS del SAT.
     */
    private function buildService(FslNode $node): Service
    {
        $fiel = $this->buildFiel($node);
        return new Service(new FielRequestBuilder($fiel), new GuzzleWebClient());
    }

    // ─── Métodos públicos ─────────────────────────────────────────────────────

    /**
     * Valida que un XML sea bien formado (parseable por SimpleXML).
     */
    public function validateLocalXml(string $xmlContent): array
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xmlContent);
        $isValid = $doc !== false;
        libxml_clear_errors();
        return ['is_valid' => $isValid];
    }

    /**
     * Envía la solicitud de descarga masiva al SAT y devuelve el ticket (requestId).
     * Devuelve null si el SAT rechaza la petición o si ocurre un error.
     */
    public function requestDownload(
        string $rfc,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        FslNode $node
    ): ?string {
        Log::info("Conectando al WebService real del SAT para el RFC: {$rfc}");

        try {
            $service = $this->buildService($node);

           // DateTimePeriod necesita el DateTime propio de phpcfdi
            $satStart = SatDateTime::create($start->format('Y-m-d\TH:i:s'));
            $satEnd   = SatDateTime::create($end->format('Y-m-d\TH:i:s'));

            // Construimos la consulta usando métodos encadenados (Fluent Interface).
            // Esto elimina por completo el error "Unknown named parameter".
            $parameters = QueryParameters::create()
                ->withPeriod(DateTimePeriod::create($satStart, $satEnd))
                ->withDownloadType(DownloadType::received())
                ->withRequestType(RequestType::xml())
                ->withDocumentStatus(DocumentStatus::active());

            $query = $service->query($parameters);

            if (!$query->getStatus()->isAccepted()) {
                Log::error("El SAT rechazó la petición: " . $query->getStatus()->getMessage());
                return null;
            }

            $ticketId = $query->getRequestId();
            Log::info("Consulta aceptada por el SAT. Ticket: {$ticketId}");

            return $ticketId;

        } catch (\Exception $e) {
            Log::error("Error en requestDownload: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica el estatus de un ticket en el SAT.
     * Devuelve la respuesta del servicio o null si ocurre un error.
     */
    public function verifyDownload(string $ticketId, FslNode $node)
    {
        try {
            return $this->buildService($node)->verify($ticketId);
        } catch (\Exception $e) {
            Log::error("Error en verifyDownload: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Descarga un paquete ZIP del SAT y lo guarda en storage temporal.
     * Devuelve la ruta absoluta del ZIP o null si ocurre un error.
     */
    public function downloadPackage(string $packageId, FslNode $node): ?string
    {
        try {
            $download = $this->buildService($node)->download($packageId);

            if (!$download->getStatus()->isAccepted()) {
                Log::error("Error descargando paquete: " . $download->getStatus()->getMessage());
                return null;
            }

            $path = storage_path('app/private/temp_sat/' . $packageId . '.zip');

            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, $download->getPackageContent());

            return $path;

        } catch (\Exception $e) {
            Log::error("Error crítico en downloadPackage: " . $e->getMessage());
            return null;
        }
    }
}
