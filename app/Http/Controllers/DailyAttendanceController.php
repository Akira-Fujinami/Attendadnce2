<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Adit;
use App\Models\Employee;
use App\Models\Event;
use App\Models\DailySummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class DailyAttendanceController extends Controller
{

    public function exportDailyAttendance(Request $request)
    {
        $currentYear = $request->input('year', now()->year);
        $currentMonth = $request->input('month', now()->month);
    
        // データ取得（同じロジックを再利用）
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();
    
        $employees = Employee::join('daily_summaries', 'employees.id', '=', 'daily_summaries.employee_id')
                    ->where('employees.company_id', $request->companyId)
                    ->select('employees.*') // 必要なら `daily_summaries.*` も追加
                    ->distinct() // 重複を防ぐ
                    ->get();
        $data = [];
        $totalSalary = 0;
    
        foreach ($employees as $employee) {
            $summary = DailySummary::where('company_id', $request->companyId)
                ->where('employee_id', $employee->id)
                ->where('date', $request->date)
                ->selectRaw('total_work_hours, total_break_hours, salary')
                ->first();
            // 総勤務時間の計算
            $workMinutes = $summary->total_work_hours % 60;     // 分部分
            $breakMinutes = $summary->total_break_hours % 60; 
            
            // 表示用
            $formattedWorkHours = sprintf('%02d時間%02d分', $summary->total_work_hours, $workMinutes);
            $formattedBreakHours = sprintf('%02d時間%02d分', $summary->total_break_hours, $breakMinutes);

            $salary = $summary->salary ?? 0;
            $totalSalary += $salary;
    
            $data[] = [
                'name' => $employee->name,
                'workHours' => $formattedWorkHours ?? 0,
                'breakHours' => $formattedBreakHours ?? 0,
                'salary' => $summary->salary ?? 0,
            ];
        }
    
        // ヘッダー行を含むデータを準備
        $csvData = [];
        $csvData[] = ['名前', '労働時間', '休憩時間', '給与'];
    
        foreach ($data as $row) {
            $csvData[] = [
                $row['name'],
                $row['workHours'],
                $row['breakHours'],
                '¥' . number_format($row['salary']),
            ];
        }
        $csvData[] = ['', '', '全体の総給与', '¥' . number_format($totalSalary)];
    
        // CSVを出力
        $currentDateTime = now()->format('Ymd_His'); // 例: 20231231_123456
        $fileName = "attendance_list_{$currentDateTime}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];
        // dd($data);
    
        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOMを追加
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOMを追加
            foreach ($csvData as $line) {
                fputcsv($file, $line); // UTF-8のまま出力
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function showDailyAttendance($date)
    {
        // 選択された日付
        $selectedDate = Carbon::parse($date);

        // 全従業員の打刻データを取得
        $employees = Employee::join('adit_logs', 'employees.id', '=', 'adit_logs.employee_id')
        ->where('employees.company_id', Auth::user()->id) // 現在の会社の従業員
        ->whereDate('adit_logs.date', $selectedDate->toDateString()) // 選択した日付の打刻データ
        ->selectRaw(
            'employees.id as employee_id, 
             employees.name, 
             employees.company_id'
        )
        ->groupBy('employees.id', 'employees.name', 'employees.company_id') // 必要なカラムをすべて GROUP BY に追加
        ->get();
        $attendanceData = [];
        $totalSalary = 0;

        foreach ($employees as $employee) {

            // 給与の計算
            $summary = DailySummary::where('company_id', Auth::User()->id)
            ->where('employee_id', $employee->employee_id)
            ->where('date', $selectedDate->toDateString())
            ->selectRaw('total_work_hours, total_break_hours, salary')
            ->first();
            $totalDailySalary = $summary->salary ?? 0;
            $totalDailyWorkHours = $summary->total_work_hours ?? 0;
            $totalDailyBreakHours = $summary->total_break_hours ?? 0;
            $totalSalary += $totalDailySalary;
            $error = AditController::error(Auth::User()->id, $employee->employee_id, $selectedDate->toDateString());

            $attendanceData[] = [
                'employee' => $employee,
                'totalDailySalary' => $totalDailySalary,
                'totalDailyWorkHours' => $totalDailyWorkHours,
                'totalDailyBreakHours' => $totalDailyBreakHours,
                'error' => $error
            ];
        }

        return view('dailyAttendance', [
            'selectedDate' => $selectedDate->toDateString(),
            'attendanceData' => $attendanceData,
            'totalSalary' => $totalSalary,
        ]);
    }
}