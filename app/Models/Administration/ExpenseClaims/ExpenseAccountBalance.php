<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ExpenseAccountBalance extends Model
{
    protected $table = 'expense_account_balances';

    protected $fillable = [
        'user_id',
        'balance_amount',
        'last_movement_at'
    ];

    protected $casts = [
        'balance_amount' => 'decimal:2',
        'last_movement_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ledgers()
    {
        return $this->hasMany(ExpenseAccountTransaction::class, 'user_id', 'user_id');
    }
}
