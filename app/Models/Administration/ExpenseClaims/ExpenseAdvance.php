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
        'created_by_id', // Nuevo campo
        'approver_id',
        'advance_date',
        'advance_reason',
        'advance_type',
        'description',
        'amount',
        'balance',
        'status',
        'settlement_status',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class, 'expense_advance_id');
    }
}



