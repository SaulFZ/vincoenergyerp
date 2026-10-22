<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmployeeCard extends Model
{
    protected $fillable = [
        'employee_id',
        'token',
        'visible_fields',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'visible_fields' => 'array',
        'is_active'      => 'boolean',
        'expires_at'     => 'datetime',
    ];

    /**
     * Whitelist de campos permitidos en la tarjeta pública.
     * NUNCA añadas aquí: password, salary, curp, rfc, nss,
     * address, bank_account, medical_*, role_id, permissions.
     */
    public const ALLOWED_FIELDS = [
        'full_name',
        'photo',
        'position',
        'department',
        'email',
        'phone_work',
        'employee_number',
    ];

    public const DEFAULT_FIELDS = [
        'full_name',
        'photo',
        'position',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function tokenFor(Employee $employee): string
    {
        return static::firstOrCreate(
            ['employee_id' => $employee->id],
            [
                'token'          => Str::random(40),
                'visible_fields' => self::DEFAULT_FIELDS,
            ]
        )->token;
    }

   /**
 * Devuelve solo los campos permitidos y visibles para la tarjeta pública.
 * Si el empleado fue eliminado (o no existe), devuelve un arreglo vacío.
 */
public function visibleData(): array
{
    if (! $this->employee) {
        return [];
    }

    $fields = array_values(array_intersect(
        $this->visible_fields ?? self::DEFAULT_FIELDS,
        self::ALLOWED_FIELDS
    ));

    if (empty($fields)) {
        $fields = self::DEFAULT_FIELDS;
    }

    return $this->employee->only($fields);
}

    public function isAccessible(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function scopeAccessible($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}