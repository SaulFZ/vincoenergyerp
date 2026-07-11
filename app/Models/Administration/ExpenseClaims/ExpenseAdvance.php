<?php

namespace App\Models\Administration\ExpenseClaims;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseAdvance extends Model
{
    protected $table = 'expense_advances';

    protected $fillable = [
        'folio_system',
        'user_id',
        'advance_date',
        'advance_type',
        'description',
        'amount',
        'balance',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'advance_date' => 'date',
            'amount'       => 'decimal:2',
            'balance'      => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class, 'expense_advance_id');
    }
}
