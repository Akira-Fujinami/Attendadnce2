<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Adit;
use App\Models\DailySummary;
use Carbon\Carbon;

class AditController extends Controller
{
    public function index()
    {
        $user = Auth::User();
        $today = Carbon::today()->toDateString(); // 本日の日付を取得（フォーマット：Y-m-d）

        // Adit_logsテーブルからadit_itemがwork_startのデータを取得
        $aditExists = Adit::whereDate('date', $today)
            ->where('employee_id', $user->id)
            ->where('company_id', $user->company_id)
            ->exists();
        $latestAdit = Adit::whereDate('date', $today)
            ->orderBy('created_at', 'desc') // 最新順にソート
            ->first();
        $status = null;
        if ($latestAdit) {
            if ($latestAdit->adit_item === 'work_start') {
                $status = '出勤中';
            } elseif ($latestAdit->adit_item === 'break_start') {
                $status = '休憩中';
            } elseif ($latestAdit->adit_item === 'break_end') {
                $status = '出勤中';
            } elseif ($latestAdit->adit_item === 'work_end') {
                $status = '退勤済み';
            }
        }

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        
        // 対象期間の全ての `Adit` レコードを一度に取得
        $aditRecords = Adit::whereBetween('date', [$lastMonthStart, $yesterday])
            ->where('employee_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', '!=', 'rejected')
            ->get()
            ->groupBy('date');
        
        // 退勤打刻がない日付をチェック
        $missingWorkEndDates = collect();
        
        foreach ($aditRecords as $date => $records) {
            $workStartExists = $records->contains('adit_item', 'work_start');
            $breakStartExists = $records->contains('adit_item', 'break_start');
            $breakEndExists = $records->contains('adit_item', 'break_end');
            $workEndExists = $records->contains('adit_item', 'work_end');
        
            if (($workStartExists || $breakStartExists || $breakEndExists) && !$workEndExists) {
                $missingWorkEndDates->push($date);
            }
        }
        $errors = [];
        // エラーに追加
        foreach ($missingWorkEndDates as $missingDate) {
            $errors[] = [
                'date' => $missingDate,
                'error' => '退勤打刻がありません',
            ];
        }        
        // dd($errors);
        

        // dd($latestAdit);
        $data = [
            'name' => $user->name,
            'status' => $status,
            'latestAdit' => $latestAdit ? $latestAdit->adit_item : null,
            'aditExists' => $aditExists,
            'errors' => $errors,
        ];
        // dd($data);

        return view('adit', compact('data'));
    }
    public function adit(Request $request) {
        Adit::create([
            'company_id' => $request->company_id,
            'employee_id' => $request->employee_id,
            'date' => now()->format('Y-m-d'),
            'minutes' => now(),
            'adit_item' => $request->adit_item,
            'status' => 'approved',
        ]);
        $dailySummary = DailySummary::firstOrCreate(
            [
                'company_id' => $request->company_id,
                'employee_id' => $request->employee_id,
                'date' => now()->format('Y-m-d'),
            ],
            [
                'company_id' => $request->company_id,
                'employee_id' => $request->employee_id,
                'date' => now()->format('Y-m-d'),
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0,
                'salary' => 0,
                'error_types' => null,
            ]
        );
        $today = now()->format('Y-m-d');
        $aditExists = Adit::whereDate('date', $today)
                        ->where('company_id', $request->company_id)
                        ->where('employee_id', $request->employee_id)
                        ->exists();
        if ($aditExists) {
            $totalBreakHours = $this->calculateBreakHours($request->company_id, $request->employee_id, $today);
            $totalWorkHours = $this->calculateWorkHours($request->company_id, $request->employee_id, $today, $totalBreakHours);
            // 給与を計算
            $salary = $this->calculateSalary($request->wage, $request->transportation, $totalWorkHours, $totalBreakHours);
    
            $dailySummary->update([
                'total_work_hours' => $totalWorkHours,
                'total_break_hours' => $totalBreakHours,
                'overtime_hours' => max($totalWorkHours - 8, 0), // 8時間以上の場合は残業
                'salary' => $salary, // 給与計算ロジック
            ]);
        }
        return redirect()->route('adit');
    }

    public static function calculateWorkHours($companyId, $employeeId, $date, $breakHours)
    {
        $workStart = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->where('adit_item', 'work_start')
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->where('deleted', 0)
            ->orderBy('created_at', 'asc')
            ->first();
        // dd($workStart);

        $workEnd = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->where('adit_item', 'work_end')
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->where('deleted', 0)
            ->orderBy('created_at', 'desc')
            ->first();
            // dd($workEnd);

        if ($workStart && $workEnd) {
            $totalWorkHours = \Carbon\Carbon::parse($workStart->minutes)->diffInHours(\Carbon\Carbon::parse($workEnd->minutes));
            return ($totalWorkHours - $breakHours);
        }

        return 0;
    }

    public static function calculateBreakHours($companyId, $employeeId, $date)
    {
        // 休憩開始データを取得
        $breakStarts = Adit::where('employee_id', $employeeId)
            ->where('adit_item', 'break_start')
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->where('deleted', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    
        // 休憩終了データを取得
        $breakEnds = Adit::where('employee_id', $employeeId)
            ->where('adit_item', 'break_end')
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->where('deleted', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    
        // 休憩時間を計算する
        $totalBreakHours = 0;
        $count = min($breakStarts->count(), $breakEnds->count()); // ペアとして計算する数を決定
    
        for ($i = 0; $i < $count; $i++) {
            $breakStartTime = Carbon::parse($breakStarts[$i]->minutes);
            $breakEndTime = Carbon::parse($breakEnds[$i]->minutes);
            $totalBreakHours += $breakStartTime->diffInMinutes($breakEndTime) / 60; // 時間に変換して加算
        }
    
        return $totalBreakHours;
    }    

    public static function calculateSalary($wage, $transportation, $totalWorkHours)
    {
        // 給与を計算
        if ($totalWorkHours <= 0) {
            return 0;
        }
        $regularHours = min($totalWorkHours, 8); // 通常の勤務時間は最大8時間
        $overtimeHours = max($totalWorkHours - 8, 0); // 8時間を超えた分
    
        $regularPay = $regularHours * $wage; // 通常の給与
        $overtimePay = $overtimeHours * $wage * 1.25; // 1.25倍の割増給与
    
        $salary = $regularPay + $overtimePay + $transportation;
    
        return $salary;
    }

}
