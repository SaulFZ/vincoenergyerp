<?php

namespace App\Models\Administration\ExpenseClaims;

use App\Models\Auth\User; // <-- RUTA CORREGIDA
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseClaimLog extends Model
{
    protected $table = 'expense_claim_logs';

    protected $fillable = [
        'expense_claim_id', 'user_id', 'action',
        'previous_status', 'new_status', 'comments'
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
