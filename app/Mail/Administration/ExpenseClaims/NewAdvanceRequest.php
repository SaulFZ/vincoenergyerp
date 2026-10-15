<?php

namespace App\Mail\Administration\ExpenseClaims;

use App\Models\Administration\ExpenseClaims\ExpenseAdvance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAdvanceRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $advance;

    public function __construct(ExpenseAdvance $advance)
    {
        $this->advance = $advance;
    }

    public function build()
    {
        return $this->subject('Nueva Solicitud de Anticipo | VesCore')
                    //CORRECCIÓN: "emails" en plural coincidiendo con tu carpeta física
                    ->view('emails.administration.expense_claims.new_advance_request');
    }
}
