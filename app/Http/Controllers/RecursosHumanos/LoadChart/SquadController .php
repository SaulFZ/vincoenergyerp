<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class SquadController extends Controller
{
    public function index()
    {
        // Obtener solo a los empleados del departamento de 'Operaciones'
        $operadores = Employee::where('department', 'Operaciones')
            ->select('id', 'employee_number', 'full_name') // Asegúrate de incluir el ID
            ->orderBy('full_name')
            ->get();

        // Puedes pasar los operadores directamente a la vista
        return view('modulos.recursoshumanos.sistemas.loadchart.approval', [
            'operadores' => $operadores
        ]);
    }
}
