<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\FslNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FslNodeController extends Controller
{
    public function index()
    {
        // Traemos todos los nodos ordenados por ID descendente (el más nuevo primero)
        $nodes = FslNode::orderBy('id', 'desc')->get();

        // Obtenemos el activo para resaltarlo si es necesario
        $activeNode = $nodes->where('is_live', true)->first();

        return view('modules.administration.expense-claims.sat-config', compact('activeNode', 'nodes'));
    }

    public function store(Request $request)
    {
        // Determinamos si es una actualización evaluando si viene el node_id
        $isUpdate = $request->filled('node_id');

        $validator = Validator::make($request->all(), [
            'node_id'  => 'nullable|exists:fsl_nodes,id',
            'entity_n' => 'required|string|max:255',
            'gov_id'   => 'required|string|max:13',
            'start_d'  => 'required|date',
            'end_d'    => 'required|date',
            // Si es actualización, password y archivos son opcionales
            's_token'  => $isUpdate ? 'nullable|string' : 'required|string',
            'doc_c'    => $isUpdate ? 'nullable|file' : 'required|file',
            'doc_k'    => $isUpdate ? 'nullable|file' : 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan campos, fechas incorrectas o los archivos son inválidos.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $storagePath = 'private/administration/expense-claims/sys-nodes';

            if ($isUpdate) {
                // ── FLUJO DE ACTUALIZACIÓN (mantiene el mismo ID) ─────────────────

                $node = FslNode::findOrFail($request->node_id);
                $node->e_name     = $request->entity_n;
                $node->g_id       = strtoupper($request->gov_id);
                $node->start_date = $request->start_d;
                $node->end_date   = $request->end_d;

                if ($request->filled('s_token')) {
                    $node->sec_token = Crypt::encryptString($request->s_token);
                }

                if ($request->hasFile('doc_c')) {
                    // Eliminar archivo anterior si existe
                    if ($node->c_bin && Storage::exists($node->c_bin)) {
                        Storage::delete($node->c_bin);
                    }
                    // Guardar preservando la extensión original (.cer)
                    $node->c_bin = $request->file('doc_c')->storeAs(
                        $storagePath,
                        $request->file('doc_c')->getClientOriginalName()
                    );
                }

                if ($request->hasFile('doc_k')) {
                    // Eliminar archivo anterior si existe
                    if ($node->k_bin && Storage::exists($node->k_bin)) {
                        Storage::delete($node->k_bin);
                    }
                    // Guardar preservando la extensión original (.key)
                    $node->k_bin = $request->file('doc_k')->storeAs(
                        $storagePath,
                        $request->file('doc_k')->getClientOriginalName()
                    );
                }

                $node->save();

                $message = 'El nodo de seguridad ha sido actualizado exitosamente.';

            } else {
                // ── FLUJO DE CREACIÓN / RENOVACIÓN (nuevo ID, desactiva los demás) ─

                FslNode::where('is_live', true)->update(['is_live' => false]);

                // Guardar preservando la extensión original (.cer / .key)
                $cPath = $request->file('doc_c')->storeAs(
                    $storagePath,
                    $request->file('doc_c')->getClientOriginalName()
                );
                $kPath = $request->file('doc_k')->storeAs(
                    $storagePath,
                    $request->file('doc_k')->getClientOriginalName()
                );

                $encryptedToken = Crypt::encryptString($request->s_token);

                FslNode::create([
                    'g_id'       => strtoupper($request->gov_id),
                    'e_name'     => $request->entity_n,
                    'c_bin'      => $cPath,
                    'k_bin'      => $kPath,
                    'sec_token'  => $encryptedToken,
                    'start_date' => $request->start_d,
                    'end_date'   => $request->end_d,
                    'is_live'    => true,
                ]);

                $message = 'El nodo seguro ha sido configurado y encriptado exitosamente en el sistema.';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar el nodo de seguridad. ' . $e->getMessage()
            ], 500);
        }
    }
}
