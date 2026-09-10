<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cost_centers';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ── RELACIONES ──

    /**
     * Proyectos o subcentros asignados a este centro de costos.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'cost_center_id');
    }

    /**
     * Reembolsos/comprobaciones imputados directamente a este centro de costos.
     */
    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class, 'cost_center_id');
    }

    /**
     * Líneas de comprobación individuales imputadas a este centro de costos.
     */
    public function expenseClaimLines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class, 'cost_center_id');
    }

    // ── SCOPES ──

    /**
     * Filtrar únicamente los centros de costo habilitados para captura.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
