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
        'phone',
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
     * Devuelve solo los campos que están en visible_fields
     * Y en la whitelist fija. Si alguien manipuló la BD, no pasa.
     */
    public function visibleData(): array
    {
        // 1. Prevenir error si el empleado fue eliminado
        if (! $this->employee) {
            return [];
        }

        // 2. Prevenir TypeError si el cast de array falló y llegó un JSON en string
        $visibleFields = is_string($this->visible_fields) 
            ? json_decode($this->visible_fields, true) 
            : $this->visible_fields;

        $fieldsToCheck = (is_array($visibleFields) && !empty($visibleFields)) 
            ? $visibleFields 
            : self::DEFAULT_FIELDS;

        $fields = array_intersect($fieldsToCheck, self::ALLOWED_FIELDS);

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