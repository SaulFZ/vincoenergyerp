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
        'folio_system', 'folio_user', 'claim_date', 'request_type', 'category', 'is_deductible',
        'user_id', 'created_by_id', 'expense_advance_id', 'area', 'cost_center',
        'emission_place', 'motive', 'total_subtotal',
        'total_iva', 'total_ish', 'total_amount',
        'evidence_documents', 'status_review', 'status_payment'
    ];

    protected function casts(): array
    {
        return [
            'claim_date'         => 'date',
            'total_subtotal'     => 'decimal:2',
            'total_iva'          => 'decimal:2',
            'total_ish'          => 'decimal:2',
            'total_amount'       => 'decimal:2',
            'evidence_documents' => 'array',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function expenseAdvance(): BelongsTo
    {
        return $this->belongsTo(ExpenseAdvance::class, 'expense_advance_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class, 'expense_claim_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ExpenseClaimLog::class, 'expense_claim_id');
    }
}
