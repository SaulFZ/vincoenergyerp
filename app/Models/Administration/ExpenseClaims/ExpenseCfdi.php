<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCfdi extends Model
{
    protected $table = 'expense_cfdis';

    protected $fillable = [
        'fsl_node_id', 'uuid', 'serie', 'folio',
        'issuer_rfc', 'issuer_name',
        'receiver_rfc', 'receiver_name',
        'subtotal', 'total', 'tax_iva', 'tax_ish', 'tax_retenciones',
        'currency', 'issue_date', 'cfdi_type', 'payment_method',
        'payment_form', 'use_cfdi', 'concept_summary',
        'sat_status', 'is_reimbursed', 'xml_path'
    ];

    protected function casts(): array
    {
        return [
            'issue_date'      => 'datetime',
            'subtotal'        => 'decimal:2',
            'total'           => 'decimal:2',
            'tax_iva'         => 'decimal:2',
            'tax_ish'         => 'decimal:2',
            'tax_retenciones' => 'decimal:2',
            'is_reimbursed'   => 'boolean',
        ];
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(FslNode::class, 'fsl_node_id');
    }

    public function claimLines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class, 'expense_cfdi_id');
    }
}
