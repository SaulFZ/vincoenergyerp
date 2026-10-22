<?php

namespace App\Http\Controllers\Systems\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCard;
use Illuminate\Support\Facades\Storage;

class EmployeeCardController extends Controller
{
    /**
     * Muestra la tarjeta de presentación pública del empleado.
     */
    public function show(string $token)
    {
        // Cargamos al empleado y a su usuario asociado para obtener el correo institucional
        $card = EmployeeCard::with(['employee.user'])
            ->whereHas('employee') // Asegurar de que la tarjeta pertenece a un empleado existente
            ->where('token', $token)
            ->accessible()
            ->firstOrFail();

        // Enviamos el modelo del empleado completo a la vista
        return view('modules.public.employee-card', [
            'employee' => $card->employee,
            'token'    => $token,
        ]);
    }

    /**
     * Muestra la foto de perfil del empleado si está permitida y existe.
     */
    public function photo(string $token)
    {
        $card = EmployeeCard::with('employee')
            ->whereHas('employee')
            ->where('token', $token)
            ->accessible()
            ->firstOrFail();

        // Aseguramos decodificación por si falló el cast a array
        $visibleFields = is_string($card->visible_fields) 
            ? json_decode($card->visible_fields, true) 
            : ($card->visible_fields ?? []);

        if (! is_array($visibleFields) || ! in_array('photo', $visibleFields, true)) {
            abort(404);
        }

        $path = $card->employee->photo;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path), [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}