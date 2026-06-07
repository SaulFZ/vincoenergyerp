<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Auth\User;

class ExpenseClaim extends Model
{
    protected $table = 'expense_claims';

    protected $fillable = [
        'folio_system',
        'folio_user',
        'category',
        'user_id',
        'created_by_id',
        'area',
        'cost_center',
        'motive',
        'total_amount',
        'status_review',
        'status_payment',
        'rejection_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    // Relación hacia el beneficiario del dinero
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación hacia quien tecleó la solicitud (Auditoría)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class, 'expense_claim_id');
    }
}
