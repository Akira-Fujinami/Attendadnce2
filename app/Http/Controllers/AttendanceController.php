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

        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
    
        // 月が0以下なら前の年の12月にする
        if ($month < 1) {
            $year--;
            $month = 12;
        }
    
        if ($month > 12) {
            $year++;
            $month = 1;
        }
        // 月初と月末を設定
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        // データベースから打刻情報を取得
        $attendanceRecords = Adit::where('employee_id', $employeeId)
                            ->where('company_id', $companyId)
                            ->whereBetween('date', [reset($dates), end($dates)])
                            ->whereIn('status', ['approved', 'pending'])
                            ->orderBy('created_at', 'desc') // 最新の順に並べる
                            ->get()
                            ->groupBy('date') // 日付ごとにグループ化
                            ->map(function ($records, $date) {
                                $latestRecord = $records->first(); // 各グループで最新のレコード
                                $hasPending = $records->contains('status', 'pending'); // statusがpendingのものがあるか
                        
                                return [
                                    'latest_record' => $latestRecord,
                                    'has_pending' => $hasPending,
                                ];
                            });
                            // dd($attendanceRecords);

        $totalWorkHours = 0;
        $totalBreakHours = 0;
        
        foreach ($dates as $date) {
            if (isset($attendanceRecords[$date])) {
                $dailyRecords = collect($attendanceRecords[$date]);
                $workStart = $dailyRecords->firstWhere('adit_item', 'work_start');
                $workEnd = $dailyRecords->firstWhere('adit_item', 'work_end');
                $breakStart = $dailyRecords->firstWhere('adit_item', 'break_start');
                $breakEnd = $dailyRecords->firstWhere('adit_item', 'break_end');
        
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
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    public function attendanceList(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
    
        // 月が0以下なら前年の12月にする
        if ($month < 1) {
            $year--;
            $month = 12;
        }
    
        // 月が13以上なら翌年の1月にする
        if ($month > 12) {
            $year++;
            $month = 1;
        }
    
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString(); // 月初
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString(); 
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
        return view('attendanceList', [
            'employees' => $employees,
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    public function attendanceDetail($employeeId, $year, $month)
    {
        // スタッフ情報を取得
        $employee = Employee::findOrFail($employeeId);

        if ($month < 1) {
            $year--;
            $month = 12;
        }
    
        // 月が13以上なら翌年の1月にする
        if ($month > 12) {
            $year++;
            $month = 1;
        }
    
        // 月初と月末の日付を取得
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

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
        $records = Adit::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->orderBy('created_at', 'asc') // 古い順にソート
            ->get();

        // 出勤、休憩開始、休憩終了、退勤に分ける
        $aditItems = ['work_start', 'break_start', 'break_end', 'work_end'];
        $data = [];

        foreach ($aditItems as $item) {
            $filteredRecords = $records->where('adit_item', $item);
            // dd($filteredRecords);
            if ($filteredRecords->count() === 1) {
                $data[$item] = [
                    'previousRecord' => $filteredRecords->where('status', 'approved')->last(), // 最新の承認済み
                    'currentRecord' => $filteredRecords->where('status', 'pending')->last(), // 最新のレコード
                ];
               
            } else {
                $data[$item] = [
                    'previousRecord' => null,
                    'currentRecord' => null,
                ];
            }
        }

        // Bladeに渡すデータ
        return view('editAttendance', [
            'date' => $date,
            'employeeId' => $employeeId,
            'records' => $data,
        ]);
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
    
        // 入力データから null を除外
        $aditItems = array_filter($aditItems, function ($time) {
            return !is_null($time);
        });
    
        // 対象のレコードを取得
        $attendanceRecord = Adit::where('employee_id', $request->employeeId)
            ->where('company_id', $request->companyId)
            ->whereDate('date', $request->date)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'pending')
            ->first();
    
        if ($attendanceRecord) {
            if (empty($aditItems)) {
                // 入力データが空の場合、削除フラグを設定
                // dd($attendanceRecord);
                $attendanceRecord->update([
                    'deleted' => 1,
                ]);
            } else {
                // 既存レコードがある場合は更新
                foreach ($aditItems as $aditItem => $time) {
                    $attendanceRecord->update([
                        'minutes' => \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $time),
                        'deleted' => 0, // 削除フラグをリセット
                    ]);
                }
            }
        } else {
            // レコードが存在しない場合は新規作成
            foreach ($aditItems as $aditItem => $time) {
                Adit::create([
                    'company_id' => $request->companyId,
                    'employee_id' => $request->employeeId,
                    'date' => $request->date,
                    'minutes' => \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $time),
                    'adit_item' => $aditItem,
                    'status' => 'pending',
                    'deleted' => 0, // 削除フラグを初期化
                ]);
            }
        }
    
        return back();
    }
}