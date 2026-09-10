<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseClaimLine extends Model
{
    protected $table = 'expense_claim_lines';

    protected $fillable = [
        'expense_claim_id', 'expense_cfdi_id', 'load_method', 'concept_group',
        'expense_date', 'document_number', 'description',
        'amount_fiscal', 'amount_simple', 'amount_none',
        'tax_ish', 'tax_iva', 'line_total',
        'cost_center_id', 'project_id', // 👈 Nuevos campos
        'is_deductible', 'accounting_account',
    ];

    protected function casts(): array
    {
        return [
            'expense_date'  => 'date',
            'amount_fiscal' => 'decimal:2',
            'amount_simple' => 'decimal:2',
            'amount_none'   => 'decimal:2',
            'tax_ish'       => 'decimal:2',
            'tax_iva'       => 'decimal:2',
            'line_total'    => 'decimal:2',
            'is_deductible' => 'boolean',
        ];
    }

    // ── RELACIONES ──

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function cfdi(): BelongsTo
    {
        return $this->belongsTo(ExpenseCfdi::class, 'expense_cfdi_id');
    }

    /**
     * Relación con el Centro de Costos a nivel Partida (Línea)
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    /**
     * Relación con el Proyecto a nivel Partida (Línea)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
