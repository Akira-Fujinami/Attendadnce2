<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Adit;
use Carbon\Carbon;

class AditController extends Controller
{
    public function adit(Request $request) {
        Adit::create([
            'company_id' => $request->company_id,
            'employee_id' => $request->employee_id,
            'date' => now()->format('Y-m-d'),
            'minutes' => now(),
            'adit_item' => $request->adit_item,
            'status' => 'approved',
        ]);
        return back();
    }
}
