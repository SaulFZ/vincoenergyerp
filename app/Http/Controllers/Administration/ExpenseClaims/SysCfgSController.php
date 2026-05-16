<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\SysCfgS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class SysCfgSController extends Controller
{
    public function index()
    {
        $ax = SysCfgS::where('st', true)->first();
        return view('modules.administration.expense-claims.s-cfg', compact('ax'));
    }

    public function store(Request $request)
    {
        // 1. Validaciones con los campos ofuscados que llegan del formulario
        $validator = Validator::make($request->all(), [
            'c2' => 'required|string|max:255',
            'c1' => 'required|string|max:13',
            's_k' => 'required|string',
            'f1' => 'required|file',
            'f2' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'rc' => 0,
                'msg' => 'Data integrity error.',
                'err' => $validator->errors()
            ], 422);
        }

        try {
            // 2. Desactivar anterior
            SysCfgS::where('st', true)->update(['st' => false]);

            // 3. Guardar en ruta ofuscada dentro de private
            $p1 = $request->file('f1')->store('private/administration/expense-claims/sys-d');
            $p2 = $request->file('f2')->store('private/administration/expense-claims/sys-d');

            // 4. Encriptar
            $k_hash = Crypt::encryptString($request->s_k);

            // 5. Guardar
            SysCfgS::create([
                'c1' => strtoupper($request->c1),
                'c2' => $request->c2,
                'f1_p' => $p1,
                'f2_p' => $p2,
                's_k' => $k_hash,
                'st' => true,
            ]);

            return response()->json([
                'rc' => 1,
                'msg' => 'Operation completed.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'rc' => 0,
                'msg' => 'Server error: 0x88F.'
            ], 500);
        }
    }
}
