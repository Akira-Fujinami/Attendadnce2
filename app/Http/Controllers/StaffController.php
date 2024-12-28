<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $user = Auth::User();


        // staff ページで表示する情報
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'スタッフ',
            'status' => $user->status ?? '在職中',
        ];

        return view('staff', compact('data'));
    }
    public function create(Request $request) {
        Employee::create([
            'company_id' => Auth::User()->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff',
            'hourly_wage' => $request->wage,
            'transportation_fee' => $request->transportation,
            'retired' => $request->status,
        ]);
        return redirect()->route('staff');
    }
}
