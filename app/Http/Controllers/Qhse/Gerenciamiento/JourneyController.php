<?php
namespace App\Http\Controllers\Qhse\Gerenciamiento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;


class JourneyController extends Controller
{
    public function index()
    {
        // 1. Usuario autenticado con empleado
        $user = Auth::user()->load('employee');

        // 2. Empleados activos con licencias
        $employees = Employee::with('license')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($emp) {
                $license = $emp->license;

                return [
                    'id' => $emp->id,
                    'nombre' => $emp->full_name,
                    'departamento' => $emp->department,
                    // 🚗 LICENCIA DE CONDUCIR
                    'licencia_conductor_vigencia' => $license
                        ? $license->driver_license_expires_at
                        : null,
                    'licencia_conductor_permanente' => $license
                        ? (bool) $license->driver_license_is_permanent
                        : false,
                    // 🚘 CURSO MANEJO DEFENSIVO LIGERO
                    'curso_ligero_vigencia' => $license
                        ? $license->light_defensive_course_expires_at
                        : null,
                    // 🚛 CURSO MANEJO DEFENSIVO PESADO
                    'curso_pesado_vigencia' => $license
                        ? $license->heavy_defensive_course_expires_at
                        : null,
                    // 🪪 LICENCIA FEDERAL (opcional si la usas)
                    'licencia_federal_vigencia' => $license
                        ? $license->federal_license_expires_at
                        : null,
                ];
            });

        return view(
            'modulos.qhse.sistemas.gerenciamiento.journey_management',
            compact('user', 'employees')
        );
    }


}
