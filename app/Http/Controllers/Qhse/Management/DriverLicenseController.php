<?php

namespace App\Http\Controllers\Qhse\Management;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DriverLicenseController extends Controller
{
    /** Llave pública (la que usa el front) => columna en la tabla de licencias */
    private const COLUMNS = [
        'driver_license'  => 'driver_license_expires_at',
        'light_course'    => 'light_defensive_course_expires_at',
        'federal_license' => 'federal_license_expires_at',
        'heavy_course'    => 'heavy_defensive_course_expires_at',
    ];

    /** Estatus filtrables desde las tarjetas */
    private const STATUSES = ['vigente', 'proxima', 'critica', 'vencida', 'sin_registro'];

    private const PER_PAGE_OPTIONS = [5, 10, 15, 20, 25, 30, 50, 100];

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 5);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 5;
        }

        $search = trim((string) $request->input('search', ''));
        $status = in_array($request->input('status'), self::STATUSES, true) ? $request->input('status') : null;
        $type   = array_key_exists((string) $request->input('type'), self::COLUMNS) ? $request->input('type') : null;

        // Si se eligió un tipo de credencial, solo se evalúa esa columna; si no, las 4.
        $columns = $type ? [self::COLUMNS[$type]] : array_values(self::COLUMNS);

        // Query base: activos + búsqueda (sin filtro de estatus) → se usa para las tarjetas
        $base = Employee::query()->where('employment_status', 'active');
        $this->applySearch($base, $search);

        $stats = $this->buildStats($base, $columns);

        // Query de la tabla: base + filtro de estatus
        $query = (clone $base)->with(['license', 'area']);
        if ($status) {
            $this->applyStatus($query, $status, $columns);
        }

        $empleados = $query
            ->orderBy('first_name')
            ->orderBy('first_surname')
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'data'       => $empleados->getCollection()->map(fn ($emp) => $this->transformEmployee($emp))->values(),
            'pagination' => [
                'current_page' => $empleados->currentPage(),
                'last_page'    => $empleados->lastPage(),
                'total'        => $empleados->total(),
                'from'         => $empleados->firstItem(),
                'to'           => $empleados->lastItem(),
            ],
            'stats'      => $stats,
            'filters'    => ['status' => $status, 'type' => $type],
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($payload);
        }

        return view('modules.qhse.management.driver_licenses', compact('payload', 'perPage'));
    }

    /* =====================================================================
     |  FILTROS
     * ===================================================================== */

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('first_surname', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT(first_name, ' ', first_surname) LIKE ?", ["%{$search}%"])
                ->orWhereHas('area', fn ($a) => $a->where('name', 'like', "%{$search}%"));
        });
    }

    /**
     * vencida      → al menos una credencial vencida
     * critica      → al menos una vence en 0–30 días
     * proxima      → al menos una vence en 31–60 días
     * vigente      → tiene al menos una fecha y TODAS las registradas vencen en más de 60 días
     * sin_registro → no tiene registro o todas las fechas están vacías
     */
    private function applyStatus(Builder $query, string $status, array $columns): void
    {
        $today = Carbon::today();

        switch ($status) {
            case 'vencida':
                $query->whereHas('license', function ($q) use ($columns, $today) {
                    $q->where(function ($w) use ($columns, $today) {
                        foreach ($columns as $c) {
                            $w->orWhereDate($c, '<', $today);
                        }
                    });
                });
                break;

            case 'critica':
                $this->whereAnyBetween($query, $columns, $today, $today->copy()->addDays(30));
                break;

            case 'proxima':
                $this->whereAnyBetween($query, $columns, $today->copy()->addDays(31), $today->copy()->addDays(60));
                break;

            case 'vigente':
                $limit = $today->copy()->addDays(60);
                $query->whereHas('license', function ($q) use ($columns, $limit) {
                    $q->where(function ($w) use ($columns) {
                        foreach ($columns as $c) {
                            $w->orWhereNotNull($c);
                        }
                    });
                    foreach ($columns as $c) {
                        $q->where(fn ($w) => $w->whereNull($c)->orWhereDate($c, '>', $limit));
                    }
                });
                break;

            case 'sin_registro':
                $query->where(function ($w) use ($columns) {
                    $w->whereDoesntHave('license')
                        ->orWhereHas('license', function ($q) use ($columns) {
                            foreach ($columns as $c) {
                                $q->whereNull($c);
                            }
                        });
                });
                break;
        }
    }

    private function whereAnyBetween(Builder $query, array $columns, Carbon $from, Carbon $to): void
    {
        $query->whereHas('license', function ($q) use ($columns, $from, $to) {
            $q->where(function ($w) use ($columns, $from, $to) {
                foreach ($columns as $c) {
                    $w->orWhere(fn ($x) => $x->whereDate($c, '>=', $from)->whereDate($c, '<=', $to));
                }
            });
        });
    }

    private function buildStats(Builder $base, array $columns): array
    {
        $stats = ['total' => (clone $base)->count()];

        foreach (self::STATUSES as $status) {
            $q = clone $base;
            $this->applyStatus($q, $status, $columns);
            $stats[$status] = $q->count();
        }

        return $stats;
    }

    /* =====================================================================
     |  TRANSFORMACIÓN
     * ===================================================================== */

    private function transformEmployee(Employee $emp): array
    {
        $license = $emp->license;
        $row = [
            'id'    => $emp->id,
            'name'  => $emp->full_name ?? trim($emp->first_name . ' ' . $emp->first_surname),
            'area'  => optional($emp->area)->name ?? 'Sin área',
            'photo' => $this->photoUrl($emp->photo),
        ];

        foreach (self::COLUMNS as $key => $column) {
            $value     = $license ? $license->{$column} : null;
            $row[$key] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
        }

        return $row;
    }

    private function photoUrl(?string $photo): ?string
    {
        if (! $photo) {
            return null;
        }

        $path = str_replace('public/', '', $photo);
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $encoded = str_replace(['+', '/'], ['-', '_'], base64_encode($path));

        return route('management.employee.photo', ['path' => $encoded]);
    }

    /* =====================================================================
     |  FOTO
     * ===================================================================== */

    public function showPhoto($path)
    {
        try {
            $decodedPath = base64_decode(str_replace(['-', '_'], ['+', '/'], $path));
            $decodedPath = str_replace('public/', '', $decodedPath);

            if (! Storage::disk('public')->exists($decodedPath)) {
                abort(404, 'Foto no encontrada');
            }

            return response()->file(Storage::disk('public')->path($decodedPath), [
                'Content-Type'        => Storage::disk('public')->mimeType($decodedPath),
                'Content-Disposition' => 'inline',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sirviendo foto de empleado: ' . $e->getMessage());
            abort(404);
        }
    }

    /* =====================================================================
     |  ACTUALIZAR
     * ===================================================================== */

    public function updateLicenses(Request $request, $id)
    {
        $data = $request->validate([
            'driver_license_expires_at'         => ['nullable', 'date'],
            'light_defensive_course_expires_at' => ['nullable', 'date'],
            'federal_license_expires_at'        => ['nullable', 'date'],
            'heavy_defensive_course_expires_at' => ['nullable', 'date'],
        ]);

        try {
            $empleado = Employee::findOrFail($id);
            $empleado->license()->updateOrCreate(['employee_id' => $empleado->id], $data);

            return response()->json([
                'success' => true,
                'message' => 'Credenciales actualizadas correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar licencias del empleado ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar actualizar los datos.',
            ], 500);
        }
    }
}
