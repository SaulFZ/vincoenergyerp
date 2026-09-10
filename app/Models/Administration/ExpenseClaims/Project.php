<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'cost_center_id',
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
        ];
    }

    // ── RELACIONES ──

    /**
     * Centro de costos padre al que pertenece el proyecto.
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    /**
     * Reembolsos/comprobaciones imputados a este proyecto en cabecera.
     */
    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class, 'project_id');
    }

    /**
     * Líneas de comprobación individuales imputadas a este proyecto.
     */
    public function expenseClaimLines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class, 'project_id');
    }

    // ── SCOPES ──

    /**
     * Filtrar únicamente los proyectos habilitados para recibir imputación de gastos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtrar proyectos según su estado operativo (Activo, Pausado, Cerrado, etc.).
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
