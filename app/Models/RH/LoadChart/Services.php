<?php

namespace App\Models\RH\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Services extends Model
{
    use HasFactory;

    /**
     * Get grouped services, ordered by specific operation type and identifier.
     *
     * @return array
     */
    public static function getGroupedServices()
    {
        // 1. Obtiene todos los servicios y los ordena por identificador de menor a mayor.
        // **IMPORTANTE: Se agregó el campo 'amount'**
        $services = self::select('operation_type', 'service_type', 'service_performed', 'identifier', 'service_description', 'amount')
            ->orderBy('identifier', 'asc')
            ->get();

        // 2. Agrupa los servicios por 'operation_type'
        $groupedServices = $services->groupBy('operation_type');

        // 3. Define el orden deseado para las claves del array.
        $order = ['Tierra', 'Marina']; // Corregido 'Marino' a 'Marina' para coincidir con tus datos

        // 4. Crea un nuevo array en el orden especificado.
        $orderedGroupedServices = [];
        foreach ($order as $type) {
            if (isset($groupedServices[$type])) {
                // Para cada tipo de operación, reagrupa por tipo de servicio y servicio realizado
                $reordered = $groupedServices[$type]->groupBy(['service_type', 'service_performed']);
                $orderedGroupedServices[$type] = $reordered;
            }
        }

        // 5. Agrega cualquier otro tipo de operación que no esté en la lista de orden.
        foreach ($groupedServices as $type => $group) {
            if (!in_array($type, $order)) {
                $reordered = $group->groupBy(['service_type', 'service_performed']);
                $orderedGroupedServices[$type] = $reordered;
            }
        }

        return $orderedGroupedServices;
    }
}
