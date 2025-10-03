<?php
namespace App\Models\RecursosHumanos\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_category',
        'bonus_type',
        'amount',
        'currency',
        'bonus_identifier',
        'is_active',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope para bonos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por categoría de empleado
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('employee_category', $category);
    }

    /**
     * Scope para buscar por tipo de bono
     */
    public function scopeByType($query, $type)
    {
        return $query->where('bonus_type', $type);
    }

    /**
     * Obtener el monto formateado con currency
     */
    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Obtener el nombre completo del bono
     */
    public function getFullBonusNameAttribute()
    {
        return $this->bonus_type . ' - ' . $this->employee_category;
    }
}
