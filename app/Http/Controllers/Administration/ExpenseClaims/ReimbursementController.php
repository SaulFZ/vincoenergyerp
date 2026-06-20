<?php
namespace App\Http\Controllers\Administration\ExpenseClaims; // Ajusta el namespace si lo guardaste en una subcarpeta

use App\Http\Controllers\Controller;

class ReimbursementController extends Controller
{
    /**
     * Muestra la vista principal del módulo de reembolsos.
     */
    public function index()
    {
        // Asegúrate de que esta ruta coincida con la ubicación de tu archivo Blade
        return view('modules.administration.expense-claims.reimbursements');
    }


}
