<?php
namespace App\Models\Administration\ExpenseClaims;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseClaim extends Model
{
    protected $table = 'expense_claims';

    protected $fillable = [
        'folio_system', 'folio_user', 'category',
        'user_id', 'created_by_id', 'area',
        'cost_center', 'emission_place', 'motive',
        'total_amount', 'status_review', 'status_payment',
        'rejection_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class, 'expense_claim_id');
    }

    /**
     * Los atributos que se deben agregar al array del modelo (Virtuales)
     */
    protected $appends = ['subtotal_neto', 'total_iva', 'total_ish'];

/**
 * Genera un campo virtual sumando los subtotales de las líneas
 */
    public function getSubtotalNetoAttribute()
    {
        return $this->lines()->sum('subtotal');
    }

/**
 * Genera un campo virtual sumando los IVAs de las líneas
 */
    public function getTotalIvaAttribute()
    {
        return $this->lines()->sum('tax_iva');
    }

/**
 * Genera un campo virtual sumando los impuestos locales de las líneas
 */
    public function getTotalIshAttribute()
    {
        return $this->lines()->sum('tax_ish');
    }
}
