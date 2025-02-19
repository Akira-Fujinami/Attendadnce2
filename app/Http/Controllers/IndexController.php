<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Adit;
use App\Models\User;
use App\Models\DailySummary;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::User();
        $query = Employee::query();
    
        // 従業員リストを取得
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $EmployeeList = $query->where('company_id', Auth::user()->id)->get();
        $errors = []; // エラー配列を初期化
        foreach ($EmployeeList as $employee) {
            $pendingRecords = Adit::where('employee_id', $employee->id)
            ->where('company_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('date') // 日付ごとにグループ化
            ->map(function ($records, $date) {
                $latestRecord = $records->first(); // 最新の未承認打刻
                return [
                    'date' => $latestRecord->date,
                    'name' => $latestRecord->employee->name ?? '未設定',
                    'status' => $latestRecord->status,
                ];
            })
            ->values() // 配列として保持
            ->toArray();
            $employee->pendingRecords = $pendingRecords;

            $aditRecords = Adit::whereBetween('date', [$lastMonthStart, $yesterday])
            ->where('company_id', $user->id)
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'rejected')
            ->with('employee')
            ->get()
            ->groupBy('date');

            foreach ($aditRecords as $date => $records) {
                $errorExist = AditController::error($user->id, $employee->id, $date);
                if ($errorExist) {
                    $errors[$employee->name][$date] = [
                        'name' => '打刻が不正です (' . $date . ')',
                        'date' => $date,
                        'company_id' => $user->id,
                        'employee_id' => $employee->id,
                    ];
                }
            }
        
            // プロパティに追加
            $employee->setAttribute('errors', $errors[$employee->name] ?? []);
        }

        return view('top', [
            'EmployeeList' => $EmployeeList,
        ]);
    }
    
}