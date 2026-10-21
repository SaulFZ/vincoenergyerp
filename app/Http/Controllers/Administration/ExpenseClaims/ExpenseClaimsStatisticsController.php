<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;

class ExpenseClaimsStatisticsController extends Controller
{
    public function index()
    {
        return view('modules.administration.expense_claims.statistics');
    }
}