<?php

namespace App\Mail\Administration\ExpenseClaims;

use App\Models\Administration\ExpenseClaims\ExpenseAdvance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class AdvanceEvaluatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $advance;

    public function __construct(ExpenseAdvance $advance)
    {
        $this->advance = $advance;
    }

    public function build()
    {
        // ✅ Solo primera letra de cada palabra
        $estado = Str::headline($this->advance->status);
        $asunto = "Dictamen de Anticipo: {$estado} | VesCore";

        return $this->subject($asunto)
                    ->view('emails.administration.expense_claims.advance_evaluated_notification');
    }
}
