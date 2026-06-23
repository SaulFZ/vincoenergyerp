<?php
namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use Illuminate\Support\Facades\Auth;

class ReimbursementController extends Controller
{
    public function index()
    {
        // Ejemplo de cómo mandar datos reales a la vista:
        // Traemos todos los reembolsos ordenados por los más recientes
        $reembolsos = ExpenseClaim::with('beneficiary')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('modules.administration.expense-claims.reimbursements', [
            'reembolsos' => $reembolsos
        ]);
    }
}
