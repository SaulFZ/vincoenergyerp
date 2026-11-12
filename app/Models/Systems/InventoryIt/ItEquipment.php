<?php

namespace App\Models\Systems\InventoryIt;

use App\Models\RH\OrgManagement\Area;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItEquipment extends Model
{
    use SoftDeletes;

    protected $table = 'it_equipment';

    protected $fillable = [
        'codigo',
        'tipo',
        'subtipo',
        'marca',
        'modelo',
        'serie',
        'serie_teclado',
        'serie_mouse',
        'serie_receptor',
        'estado',
        'condicion',
        'estatus',
        'area_id',
        'fecha_alta',
        'garantia_hasta',
        'specs',
        'observaciones',
        'created_by',
    ];

    protected $casts = [
        'fecha_alta' => 'date:Y-m-d',
        'garantia_hasta' => 'date:Y-m-d',
        'specs' => 'array',
    ];

    public const PREFIJOS = [
        'laptop' => 'VES-LAP',
        'desktop' => 'VES-DES',
        'monitor' => 'VES-MON',
        'celular' => 'VES-CEL',
        'tablet' => 'VES-TAB',
        'impresora' => 'VES-IMP',
        'redes' => 'VES-RED',
        'almacenamiento' => 'VES-ALM',
        'energia' => 'VES-ENE',
        'audiovideo' => 'VES-AV',
        'accesorio' => 'VES-ACC',
    ];

    public const ESTADOS = [
        'nuevo',
        'seminuevo',
        'usado',
        'reacondicionado',
    ];

    public const CONDICIONES = [
        'excelente',
        'bueno',
        'regular',
        'con_detalles',
        'danado',
    ];

    public const ESTATUS = [
        'disponible',
        'asignado',
        'en_prestamo',
        'reservado',
        'en_mantenimiento',
        'en_garantia',
        'deshabilitado',
        'extraviado',
        'robado',
        'baja',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(ItEquipmentComponent::class, 'it_equipment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generarSiguienteFolio(string $tipo): string
    {
        $prefix = self::PREFIJOS[$tipo] ?? 'VES-EQP';

        $codigos = self::withTrashed()
            ->where('tipo', $tipo)
            ->where('codigo', 'like', $prefix . '-%')
            ->pluck('codigo');

        $max = 0;
        foreach ($codigos as $cod) {
            if (preg_match('/-(\d+)$/', $cod, $matches)) {
                $num = (int) $matches[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $siguiente = $max + 1;

        do {
            $candidato = sprintf('%s-%03d', $prefix, $siguiente);
            $existe = self::withTrashed()->where('codigo', $candidato)->exists();
            $siguiente++;
        } while ($existe);

        return $candidato;
    }

    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'tipo' => $this->tipo,
            'subtipo' => $this->subtipo,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'serie' => $this->serie ?? '',
            'serie_teclado' => $this->serie_teclado ?? '',
            'serie_mouse' => $this->serie_mouse ?? '',
            'serie_receptor' => $this->serie_receptor ?? '',
            'estado' => $this->estado,
            'condicion' => $this->condicion,
            'estatus' => $this->estatus,
            'area_id' => $this->area_id,
            'area' => $this->area?->name ?? '',
            'fecha_alta' => $this->fecha_alta ? $this->fecha_alta->format('Y-m-d') : null,
            'garantia_hasta' => $this->garantia_hasta ? $this->garantia_hasta->format('Y-m-d') : '',
            'specs' => $this->specs ?? (object) [],
            'extras' => $this->components->map(fn (ItEquipmentComponent $c) => [
                'id' => $c->id,
                'tipo' => $c->tipo,
                'marca' => $c->marca ?? '',
                'modelo' => $c->modelo ?? '',
                'serie' => $c->serie ?? '',
                'serie_teclado' => $c->serie_teclado ?? '',
                'serie_mouse' => $c->serie_mouse ?? '',
                'serie_receptor' => $c->serie_receptor ?? '',
            ])->values()->all(),
            'observaciones' => $this->observaciones ?? '',
        ];
    }
}
