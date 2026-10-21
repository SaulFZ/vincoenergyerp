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
        'user_id', 'created_by_id', 'expense_advance_id', 'area',
        'cost_center_id', 'project_id',
        'emission_place', 'motive', 'total_subtotal',
        'total_iva', 'total_ish', 'total_amount',
        // ── SOBRANTE DEL ANTICIPO (A favor de la empresa) ──
        'leftover_amount', 'leftover_method', 'leftover_proof_path', 'leftover_notes',
        // ── EXCEDENTE DEL ANTICIPO (A favor del empleado) ──
        'excess_amount', 'excess_method', 'excess_proof_path', 'excess_notes',
        // ── ESTATUS / EVIDENCIAS ──
        'evidence_documents', 'status_review', 'status_approval', 'status_payment', 'status_balance',
    ];

    protected function casts(): array
    {
        return [
            'claim_date'         => 'date',
            'is_deductible'      => 'boolean',
            'total_subtotal'     => 'decimal:2',
            'total_iva'          => 'decimal:2',
            'total_ish'          => 'decimal:2',
            'total_amount'       => 'decimal:2',
            'leftover_amount'    => 'decimal:2',
            'excess_amount'      => 'decimal:2',
            'evidence_documents' => 'array',
        ];
    }

    // ── RELACIONES ──

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

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
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