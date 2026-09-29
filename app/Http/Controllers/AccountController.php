<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Phase 1 smoke stub — full Accounts CRUD is Phase 2 (FR-ACCT-*).
     */
    public function index(): View
    {
        return view('accounts.index');
    }
}
