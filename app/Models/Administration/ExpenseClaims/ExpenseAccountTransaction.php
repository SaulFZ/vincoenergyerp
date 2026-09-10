<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ExpenseAccountTransaction extends Model
{
    protected $table = 'expense_account_transactions';

    protected $fillable = [
        'user_id',
        'created_by_id',
        'expense_advance_id',
        'expense_claim_id',
        'folio_system',
        'movement_type',
        'amount',
        'previous_balance',
        'new_balance',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function advance()
    {
        return $this->belongsTo(ExpenseAdvance::class, 'expense_advance_id');
    }

    public function claim()
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }
}
