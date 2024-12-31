<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Adit;
use App\Models\Employee;
use App\Models\DailySummary;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request) {
        $employeeId = $request->employee_id;
        $companyId = $request->company_id;
        $employeeName = Employee::where('id', $employeeId)->where('company_id', $companyId)->first()->name;

        // 12月1日から31日までの日付を生成
        $dates = [];
        $now = Carbon::now('Asia/Tokyo');

        // 月初と月末を設定
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        // データベースから打刻情報を取得
        $attendanceRecords = Adit::where('employee_id', $employeeId)
                                ->where('company_id', $companyId)
                                ->whereBetween('date', [reset($dates), end($dates)])
                                ->get()
                                ->groupBy('date');
                                // dd($attendanceRecords);
        
        $totalWorkHours = 0;
        $totalBreakHours = 0;
        
        foreach ($dates as $date) {
            if (isset($attendanceRecords[$date])) {
                $workStart = $attendanceRecords[$date]->firstWhere('adit_item', 'work_start');
                $workEnd = $attendanceRecords[$date]->firstWhere('adit_item', 'work_end');
                $breakStart = $attendanceRecords[$date]->firstWhere('adit_item', 'break_start');
                $breakEnd = $attendanceRecords[$date]->firstWhere('adit_item', 'break_end');
        
                if ($workStart && $workEnd) {
                    $totalWorkHours += \Carbon\Carbon::parse($workStart->minutes)->diffInMinutes(\Carbon\Carbon::parse($workEnd->minutes));
                    $hours = floor($totalWorkHours / 60);
                                
                    $minutes = $totalWorkHours % 60;
                    $totalWorkHours = $hours + ($minutes / 100);
                }
        
                if ($breakStart && $breakEnd) {
                    $totalBreakHours += \Carbon\Carbon::parse($breakStart->minutes)->diffInMinutes(\Carbon\Carbon::parse($breakEnd->minutes));
                    $breakHours = floor($totalWorkHours / 60);
                                
                    $breakMinutes = $totalBreakHours % 60;
                    $totalBreakHours = $breakHours + ($breakMinutes / 100);
                }
            }
        }
        return view('attendance', [
            'dates' => $dates,
            'attendanceRecords' => $attendanceRecords,
            'name' => $employeeName,
            'employeeId' => $employeeId,
            'totalWorkHours' => $totalWorkHours,
            'totalBreakHours' => $totalBreakHours,
        ]);
    }

    public function attendanceList(Request $request)
    {
        $startDate = now()->startOfMonth()->toDateString(); // 月初 (例: 2024-12-01)
        $endDate = now()->endOfMonth()->toDateString(); 
        // 全スタッフの出勤情報を取得
        $employees = Employee::where('company_id', $request->companyId)->get();
        foreach ($employees as $employee) {
            $summary = DailySummary::where('company_id', $request->companyId)
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate]) // 月初から月末までの範囲
            ->selectRaw('SUM(total_work_hours) as totalWorkHours, COUNT(date) as attendanceDays, SUM(salary) as totalSalary')
            ->first();
            $employee->attendanceDays = $summary->attendanceDays ?? 0;
            $employee->totalWorkHours = $summary->totalWorkHours ?? 0;
            $employee->totalSalary = $summary->totalSalary ?? 0;

            $hasErrors = DailySummary::where('company_id', $request->companyId)
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('error_types')
            ->exists();
    
            $employee->hasErrors = $hasErrors;
        }

        // データをBladeに渡す
        return view('attendanceList', ['employees' => $employees]);
    }

    public function attendanceDetail($employeeId)
    {
        // スタッフ情報を取得
        $employee = Employee::findOrFail($employeeId);

        // 月初と月末の日付を取得
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();

        // DailySummaries テーブルからデータを取得
        $summaries = DailySummary::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // 出勤簿データを整理
        $attendanceData = [];
        $totalWorkHours = 0;
        $totalSalary = 0;

        foreach ($summaries as $summary) {
            $attendanceData[] = [
                'date' => $summary->date,
                'work_hours' => $summary->total_work_hours,
                'salary' => $summary->salary,
                'error' => $summary->error_types,
            ];

            $totalWorkHours += $summary->total_work_hours;
            $totalSalary += $summary->salary;
        }

        // データをBladeに渡す
        return view('attendanceDetail', [
            'employee' => $employee,
            'attendanceData' => $attendanceData,
            'totalWorkHours' => $totalWorkHours,
            'totalSalary' => (int) $totalSalary,
        ]);
    }
    public function editAttendance(Request $request)
    {
        // リクエストから日付と従業員IDを取得
        $date = $request->input('date');
        $employeeId = $request->input('employeeId');
    
        // 該当する従業員とその日付の打刻データを取得
        $attendanceRecords = Adit::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->get();
    
        // Bladeに渡すデータ
        $data = [
            'date' => $date,
            'employeeId' => $employeeId,
            'attendanceRecords' => $attendanceRecords,
        ];
    
        // editAttendanceビューを表示
        return view('editAttendance', $data);
    }
    public function updateAttendance(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'employeeId' => 'required|integer',
            'work_start' => 'nullable|date_format:H:i',
            'work_end' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
        ]);
    
        // 打刻修正のデータを保存または更新
        $aditItems = [
            'work_start' => $request->work_start,
            'work_end' => $request->work_end,
            'break_start' => $request->break_start,
            'break_end' => $request->break_end,
        ];
    
        foreach ($aditItems as $aditItem => $time) {
            if ($time) {
                Adit::updateOrCreate(
                    [
                        'employee_id' => $request->employeeId,
                        'company_id' => $request->companyId,
                        'date' => $request->date,
                        'adit_item' => $aditItem,
                    ],
                    [
                        'minutes' => \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $time),
                        'status' => 'approved', // 必要に応じてステータスを設定
                    ]
                );
            } else {
                // 該当する打刻データを削除（空の場合）
                Adit::where('employee_id', $request->employeeId)
                    ->where('company_id', $request->companyId)
                    ->where('date', $request->date)
                    ->where('adit_item', $aditItem)
                    ->delete();
            }
        }
    
        return redirect()->route('attendance', [
            'company_id' => $request->companyId,
            'employee_id' => $request->employeeId
            ]);
    }    
}