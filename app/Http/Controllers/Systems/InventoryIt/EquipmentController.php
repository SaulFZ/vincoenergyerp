<?php

namespace App\Http\Controllers\Systems\InventoryIt;

use App\Http\Controllers\Controller;
use App\Models\RH\OrgManagement\Area;
use App\Models\Systems\InventoryIt\ItEquipment;
use App\Models\Systems\InventoryIt\ItEquipmentComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(): View
    {
        $equipments = ItEquipment::with(['area:id,name,code', 'components'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (ItEquipment $eq) => $eq->toFrontendArray())
            ->values();

        $areas = Area::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('modules.systems.inventory-it.equipment', [
            'initialEquipments' => $equipments,
            'areas' => $areas,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:40', Rule::in(array_keys(ItEquipment::PREFIJOS))],
            'subtipo' => ['required', 'string', 'max:120'],
            'marca' => ['required', 'string', 'max:100'],
            'modelo' => ['required', 'string', 'max:150'],
            'serie' => ['nullable', 'string', 'max:190'],
            'serie_teclado' => ['nullable', 'string', 'max:100'],
            'serie_mouse' => ['nullable', 'string', 'max:100'],
            'serie_receptor' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:40', Rule::in(ItEquipment::ESTADOS)],
            'condicion' => ['required', 'string', 'max:40', Rule::in(ItEquipment::CONDICIONES)],
            'estatus' => ['required', 'string', 'max:40', Rule::in(ItEquipment::ESTATUS)],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'fecha_alta' => ['required', 'date'],
            'garantia_hasta' => ['nullable', 'date', 'after_or_equal:fecha_alta'],
            'specs' => ['nullable', 'array'],
            'observaciones' => ['nullable', 'string', 'max:3000'],
            'extras' => ['nullable', 'array'],
            'extras.*.tipo' => ['required_with:extras', 'string', 'max:120'],
            'extras.*.marca' => ['nullable', 'string', 'max:100'],
            'extras.*.modelo' => ['nullable', 'string', 'max:150'],
            'extras.*.serie' => ['nullable', 'string', 'max:190'],
            'extras.*.serie_teclado' => ['nullable', 'string', 'max:100'],
            'extras.*.serie_mouse' => ['nullable', 'string', 'max:100'],
            'extras.*.serie_receptor' => ['nullable', 'string', 'max:100'],
        ]);

        $seriesAValidar = collect([
            $validated['serie'] ?? null,
            $validated['serie_teclado'] ?? null,
            $validated['serie_mouse'] ?? null,
            $validated['serie_receptor'] ?? null,
        ]);

        if (!empty($validated['serie_teclado']) || !empty($validated['serie_mouse'])) {
            $seriesAValidar->shift();
        }

        foreach ($validated['extras'] ?? [] as $comp) {
            if (!empty($comp['serie_teclado']) || !empty($comp['serie_mouse'])) {
                $seriesAValidar->push($comp['serie_teclado'] ?? null);
                $seriesAValidar->push($comp['serie_mouse'] ?? null);
                $seriesAValidar->push($comp['serie_receptor'] ?? null);
            } else {
                $seriesAValidar->push($comp['serie'] ?? null);
            }
        }

        $seriesLimpias = $seriesAValidar
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values();

        if ($seriesLimpias->isNotEmpty()) {
            if ($seriesLimpias->count() !== $seriesLimpias->unique(fn ($s) => mb_strtolower($s))->count()) {
                return response()->json([
                    'message' => 'Hay números de serie repetidos dentro del mismo registro.',
                ], 422);
            }

            $duplicadaEnEquipos = ItEquipment::where(function ($q) use ($seriesLimpias) {
                $q->whereIn('serie', $seriesLimpias)
                    ->orWhereIn('serie_teclado', $seriesLimpias)
                    ->orWhereIn('serie_mouse', $seriesLimpias)
                    ->orWhereIn('serie_receptor', $seriesLimpias);
            })->first();

            if ($duplicadaEnEquipos) {
                return response()->json([
                    'message' => "El número de serie ya se encuentra registrado en el equipo {$duplicadaEnEquipos->codigo}.",
                ], 422);
            }

            $duplicadaEnComponentes = ItEquipmentComponent::with('equipment:id,codigo')
                ->where(function ($q) use ($seriesLimpias) {
                    $q->whereIn('serie', $seriesLimpias)
                        ->orWhereIn('serie_teclado', $seriesLimpias)
                        ->orWhereIn('serie_mouse', $seriesLimpias)
                        ->orWhereIn('serie_receptor', $seriesLimpias);
                })->first();

            if ($duplicadaEnComponentes) {
                $folioPadre = $duplicadaEnComponentes->equipment?->codigo ?? 'existente';
                return response()->json([
                    'message' => "Uno de los números de serie ya está registrado como accesorio en el equipo {$folioPadre}.",
                ], 422);
            }
        }

        $equipment = DB::transaction(function () use ($validated) {
            $codigo = ItEquipment::generarSiguienteFolio($validated['tipo']);

            $specs = collect($validated['specs'] ?? [])
                ->map(fn ($v) => is_string($v) ? trim($v) : $v)
                ->filter(fn ($v) => $v !== '' && $v !== null)
                ->all();

            $eq = ItEquipment::create([
                'codigo' => $codigo,
                'tipo' => $validated['tipo'],
                'subtipo' => $validated['subtipo'],
                'marca' => $validated['marca'],
                'modelo' => $validated['modelo'],
                'serie' => $validated['serie'] ?: null,
                'serie_teclado' => $validated['serie_teclado'] ?: null,
                'serie_mouse' => $validated['serie_mouse'] ?: null,
                'serie_receptor' => $validated['serie_receptor'] ?: null,
                'estado' => $validated['estado'],
                'condicion' => $validated['condicion'],
                'estatus' => $validated['estatus'],
                'area_id' => $validated['area_id'] ?? null,
                'fecha_alta' => $validated['fecha_alta'],
                'garantia_hasta' => $validated['garantia_hasta'] ?? null,
                'specs' => $specs,
                'observaciones' => $validated['observaciones'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['extras'] ?? [] as $comp) {
                $eq->components()->create([
                    'tipo' => $comp['tipo'],
                    'marca' => $comp['marca'] ?: null,
                    'modelo' => $comp['modelo'] ?: null,
                    'serie' => $comp['serie'] ?: null,
                    'serie_teclado' => $comp['serie_teclado'] ?: null,
                    'serie_mouse' => $comp['serie_mouse'] ?: null,
                    'serie_receptor' => $comp['serie_receptor'] ?: null,
                ]);
            }

            return $eq->load(['area:id,name,code', 'components']);
        });

        return response()->json([
            'message' => "Equipo {$equipment->codigo} registrado correctamente.",
            'equipment' => $equipment->toFrontendArray(),
        ], 201);
    }
}
