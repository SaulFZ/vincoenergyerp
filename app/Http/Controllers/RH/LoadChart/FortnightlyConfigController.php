<?php

namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\RH\LoadChart\FortnightlyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FortnightlyConfigController extends Controller
{
    /**
     * Obtiene la configuración de un mes y año específicos.
     *
     * @param int $year
     * @param int $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig($year, $month)
    {
        $config = FortnightlyConfig::where('year', $year)
            ->where('month', $month)
            ->first();

        if ($config) {
            return response()->json($config);
        }

        // Si no se encuentra, devuelve un objeto vacío o null
        return response()->json(null, 200);
    }

    /**
     * Guarda o actualiza la configuración de las quincenas.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'q1_start' => 'required|date',
            'q1_end' => 'required|date|after_or_equal:q1_start',
            'q2_start' => 'required|date|after:q1_end',
            'q2_end' => 'required|date|after_or_equal:q2_start',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Buscar o crear la configuración
        $config = FortnightlyConfig::updateOrCreate(
            [
                'year' => $request->year,
                'month' => $request->month
            ],
            [
                'q1_start' => $request->q1_start,
                'q1_end' => $request->q1_end,
                'q2_start' => $request->q2_start,
                'q2_end' => $request->q2_end,
            ]
        );

        return response()->json([
            'message' => 'Configuración guardada exitosamente.',
            'data' => $config
        ], 200);
    }

    /**
     * Genera una configuración por defecto para un mes y año dados.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateDefault(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        if (!$year || !$month) {
            return response()->json(['message' => 'Año y mes son requeridos.'], 400);
        }

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $config = [
            'year' => $year,
            'month' => $month,
            'q1_start' => $monthStart->format('Y-m-d'),
            'q1_end' => $monthStart->addDays(14)->format('Y-m-d'),
            'q2_start' => $monthStart->addDays(1)->format('Y-m-d'), // Este punto fue el error, se suma 1 día al final de la Q1
            'q2_end' => $monthEnd->format('Y-m-d'),
        ];

        // La lógica para la segunda quincena por defecto era incorrecta, debería empezar el día 16, no un día después de la Q1.
        // Pero tu backend de Laravel parece generar bien una lógica, así que mantendremos la tuya. La he ajustado para que empiece el día 16 por defecto.
        $config['q2_start'] = Carbon::create($year, $month, 16)->format('Y-m-d');

        return response()->json(['data' => $config], 200);
    }

    /**
     * Elimina una configuración.
     *
     * @param int $year
     * @param int $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($year, $month)
    {
        $config = FortnightlyConfig::where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$config) {
            return response()->json(['message' => 'Configuración no encontrada.'], 404);
        }

        $config->delete();

        return response()->json(['message' => 'Configuración eliminada exitosamente.'], 200);
    }
}
