<?php
namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLine;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLog;
use Carbon\Carbon;

class ReimbursementStoreController extends Controller
{
    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'motivo'       => 'required|string|max:255',
            'centro_costo' => 'required|string',
            'tipo_gasto'   => 'required|string',
            'total_amount' => 'required|numeric|min:0.01',
            'evidencias.*' => 'file|mimes:pdf|max:10240', // Max 10MB
            'lineas'       => 'required|json' // Recibimos la matriz como JSON
        ]);

        try {
            DB::beginTransaction();

            // 1. Generar Folio del Sistema (Ej: SIS2306-01)
            $datePrefix = Carbon::now()->format('dmy');
            $todayCount = ExpenseClaim::whereDate('created_at', Carbon::today())->count() + 1;
            $folioSystem = 'SIS' . $datePrefix . '-' . str_pad($todayCount, 2, '0', STR_PAD_LEFT);

            // 2. Definir estado inicial según el botón presionado
            $statusReview = $request->input('is_draft') == 'true' ? 'Borrador' : 'Pendiente';
            $statusPayment = $statusReview === 'Borrador' ? 'N/A' : 'En espera';

            // 3. Crear el Reembolso Padre
            $claim = ExpenseClaim::create([
                'folio_system'   => $folioSystem,
                'folio_user'     => 'SFP-006', // Opcional, puedes recibirlo del request
                'claim_date'     => Carbon::now()->toDateString(),
                'category'       => $request->tipo_gasto,
                'user_id'        => $request->beneficiary_id ?? Auth::id(), // Para quien es el dinero
                'created_by_id'  => Auth::id(), // Quien llenó el formato
                'area'           => $request->depto,
                'cost_center'    => $request->centro_costo,
                'emission_place' => $request->lugar_emision ?? 'VHSA, TAB.',
                'motive'         => $request->motivo,
                'total_subtotal' => $request->total_subtotal,
                'total_iva'      => $request->total_iva,
                'total_ish'      => $request->total_ish,
                'total_amount'   => $request->total_amount,
                'status_review'  => $statusReview,
                'status_payment' => $statusPayment,
                'evidence_documents' => [] // Lo llenamos en el siguiente paso
            ]);

            // 4. Guardar los Archivos Físicos (PDFs)
            $rutasPdf = [];
            if ($request->hasFile('evidencias')) {
                $carpetaDestino = "private/administration/expense-claims/pdf/{$folioSystem}";
                foreach ($request->file('evidencias') as $pdf) {
                    $rutasPdf[] = $pdf->store($carpetaDestino);
                }
                $claim->update(['evidence_documents' => $rutasPdf]);
            }

            // 5. Insertar las Líneas (El desglose financiero)
            $lineas = json_decode($request->input('lineas'), true);
            foreach ($lineas as $linea) {
                ExpenseClaimLine::create([
                    'expense_claim_id' => $claim->id,
                    'concept_group'    => $linea['categoria'], // cat-vuelos, cat-restaurantes, etc.
                    'expense_date'     => Carbon::createFromFormat('d/m/Y', $linea['fecha'])->toDateString(),
                    'document_number'  => $linea['folio'],
                    'description'      => $linea['descripcion'],
                    'amount_fiscal'    => $linea['monto_fiscal'] ?? 0,
                    'amount_simple'    => $linea['monto_simple'] ?? 0,
                    'amount_none'      => $linea['monto_sin'] ?? 0,
                    'tax_ish'          => $linea['ish'] ?? 0,
                    'tax_iva'          => $linea['iva'] ?? 0,
                    'line_total'       => $linea['total_linea'] ?? 0,
                ]);
            }

            // 6. Crear Log de Auditoría
            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => Auth::id(),
                'action'           => 'Creación',
                'previous_status'  => null,
                'new_status'       => $statusReview,
                'comments'         => 'Reembolso generado desde el portal web.'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'El reembolso fue procesado y empaquetado correctamente.',
                'folio'   => $folioSystem
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el reembolso: ' . $e->getMessage()
            ], 500);
        }
    }
}
