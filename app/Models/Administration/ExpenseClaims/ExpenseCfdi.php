<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseCfdi extends Model
{
    protected $table = 'expense_cfdis';

    protected $fillable = [
        'fsl_node_id',
        'uuid',
        'issuer_rfc',
        'issuer_name',
        'receiver_rfc',
        'subtotal',
        'total',
        'currency',
        'issue_date',
        'sat_status',
        'is_reimbursed',
        'xml_path',
        'pdf_path',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'is_reimbursed' => 'boolean',
    ];

    // Relación: Un CFDI pertenece a un Nodo de Seguridad
    public function node(): BelongsTo
    {
        return $this->belongsTo(FslNode::class, 'fsl_node_id');
    }
}
