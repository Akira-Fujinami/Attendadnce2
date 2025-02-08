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
                            ->where('status', 'approved')
                            ->orderBy('created_at', 'desc') // 最新の順に並べる
                            ->get()
                            ->groupBy('date') // 日付ごとにグループ化
                            ->map(function ($records, $date) {
                                // `$records` の中に pending ステータスがあるかを確認
                                $hasPending = $records->contains(function ($record) {
                                    return $record->status === 'pending';
                                });
                                $hasWorkStart = $records->contains('adit_item', 'work_start');
                                $hasWorkEnd = $records->contains('adit_item', 'work_end');
                        
                                // エラーフラグを追加
                                $error = AditController::error(Auth::User()->company_id, Auth::User()->id, $date);
                                $sum = DailySummary::where('employee_id', Auth::User()->id)
                                    ->where('company_id', Auth::User()->company_id)
                                    ->where('date', $date)
                                    ->first();
                                    // dd($Sum);

                                $totalBreakHours = $sum->total_break_hours ?? 0; // デフォルト値を設定
                                $totalWorkHours = $sum->total_work_hours ?? 0;   // デフォルト値を設定
                                $mappedRecords = $records->map(function ($record) {
                                    return [
                                        'id' => $record->id,
                                        'date' => $record->date,
                                        'status' => $record->status,
                                        'minutes' => $record->minutes,
                                        'adit_item' => $record->adit_item,
                                        'employee_id' => $record->employee_id,
                                    ];
                                });

                                // 各日付グループに `has_pending` を追加
                                return [
                                    'has_pending' => $hasPending,
                                    'error' => $error,
                                    'records' => $mappedRecords,
                                    'break' => $totalBreakHours,
                                    'work' => $totalWorkHours
                                ];
                            });

    
    
                            // dd($attendanceRecords);


        // 月初と月末の日付を取得
        $startDate = reset($dates); // $dates[0] が月初の日付
        $endDate = end($dates);     // $dates の最後が月末の日付

        // `daily_summaries` テーブルから月初から月末までのデータを取得
        $summaries = DailySummary::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // 各データを加算
        $totalWorkHoursMinutes = 0;
        $totalBreakHoursMinutes = 0;
        
        foreach ($summaries as $summary) {
            $totalWorkHoursMinutes += $summary->total_work_hours * 60; // 時間を分に変換して加算
            $totalBreakHoursMinutes += $summary->total_break_hours * 60; // 時間を分に変換して加算
        }
        
        // 総勤務時間の計算
        $workHours = floor($totalWorkHoursMinutes / 60); // 時間部分
        $workMinutes = $totalWorkHoursMinutes % 60;     // 分部分
        
        // 総休憩時間の計算
        $breakHours = floor($totalBreakHoursMinutes / 60); // 時間部分
        $breakMinutes = $totalBreakHoursMinutes % 60;     // 分部分
        
        // 表示用
        $formattedWorkHours = sprintf('%02d時間%02d分', $workHours, $workMinutes);
        $formattedBreakHours = sprintf('%02d時間%02d分', $breakHours, $breakMinutes);

        // dd($attendanceRecords);
        return view('attendance', [
            'dates' => $dates,
            'attendanceRecords' => $attendanceRecords,
            'name' => $employeeName,
            'employeeId' => $employeeId,
            'totalWorkHours' => $formattedWorkHours,
            'totalBreakHours' => $formattedBreakHours,
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
        $totalSalary = 0;
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
            $totalSalary += $employee->totalSalary;
        }

        // データをBladeに渡す
        return view('attendanceList', [
            'employees' => $employees,
            'currentYear' => $year,
            'currentMonth' => $month,
            'totalSalary' => $totalSalary,
        ]);
    }

    public function attendanceDetail($employeeId, $year, $month, $eventId = null)
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
        if ($eventId) {
            $event = Event::where('company_id', Auth::User()->id)
            ->where('id', $eventId)->first();
            $summaries = DailySummary::where('employee_id', $employeeId)
            ->whereBetween('date', [$event->fromDate, $event->toDate])
            ->get();
        }

        // 出勤簿データを整理
        $attendanceData = [];
        $totalWorkHours = 0;
        $totalSalary = 0;

        foreach ($summaries as $summary) {
            $error = null;
            $aditRecords = Adit::where('date', $summary->date)
            ->where('employee_id', $employeeId)
            ->get();
        
            $errorExist = AditController::error(Auth::User()->id, $employeeId, $summary->date);
            if ($errorExist) {
                $error = "打刻が不正です";
            }

            $pendingStatusExists = $aditRecords->contains('status', 'pending');
            if ($pendingStatusExists) {
                $error = "承認待ちの打刻があります";
            }
  
            $attendanceData[] = [
                'date' => $summary->date,
                'work_hours' => $summary->total_work_hours,
                'salary' => $summary->salary,
                'error' => $error,
            ];

            $totalWorkHours += $summary->total_work_hours;
            $totalSalary += $summary->salary;
        }
        usort($attendanceData, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

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
        $year = Carbon::parse($date)->year;
        $month = Carbon::parse($date)->month;
        $day = Carbon::parse($date)->day;
        // 日付が未来または明日かどうかを確認
        if (Carbon::parse($date)->isFuture() || Carbon::parse($date)->isTomorrow()) {
            return view('editAttendance', [
                'disable' => 1,
                'year' => $year,
                'month' => $month,
                'day' => $day,
            ]);
        }

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
            $filteredRecords = $records->where('adit_item', $item)->values();
            $data[$item] = [
                'previousRecord' => $filteredRecords->where('status', 'approved')->where('deleted', 0)->values()->all(), // 最新の承認済み
                'currentRecord' => $filteredRecords->where('status', 'pending')->values()->all(), // 最新のレコード
            ];            
        }
        // dd($data);

        // Bladeに渡すデータ
        return view('editAttendance', [
            'disable' => 0,
            'date' => $date,
            'employeeId' => $employeeId,
            'records' => $data,
            'year' => $year,
            'month' => $month,
            'day' => $day,
        ]);
    }

    public function updateAttendance(Request $request)
    {
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
            ->where('minutes', $request->minutes)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'pending')
            ->where('deleted', 0)
            ->first();
    
        if ($attendanceRecord) {
            if (empty($aditItems)) {
                // 入力データが空の場合、削除フラグを設定
                $attendanceRecord->update([
                    'status' => 'approved',
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
                // dd($request->adit_id);
                Adit::create([
                    'company_id' => $request->companyId,
                    'employee_id' => $request->employeeId,
                    'date' => $request->date,
                    'minutes' => \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $time),
                    'adit_item' => $aditItem,
                    'status' => 'pending',
                    'before_adit_id' => $request->adit_id ?? null,
                    'deleted' => 0, // 削除フラグを初期化
                ]);
            }
        }
    
        return back();
    }

    public function deleteAttendance(Request $request) {
        $attendanceRecord = Adit::where('employee_id', $request->employeeId)
        ->where('company_id', $request->companyId)
        ->whereDate('date', $request->date)
        ->where('adit_item', $request->adit_item)
        ->where('status', 'approved')
        ->where('deleted', 0)
        ->where('id', $request->adit_id)
        ->first();
        
        if ($attendanceRecord['status'] == 'approved') {
            $attendanceRecord->update([
                'status' => 'pending',
                'deleted' => 1,
            ]);
        }
        return back();
    }

    public function exportAttendanceList(Request $request)
    {
        $currentYear = $request->input('year', now()->year);
        $currentMonth = $request->input('month', now()->month);
    
        // データ取得（同じロジックを再利用）
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();
    
        $employees = Employee::where('company_id', $request->companyId)->get();
        $data = [];
    
        foreach ($employees as $employee) {
            $summary = DailySummary::where('company_id', $request->companyId)
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('SUM(total_work_hours) as totalWorkHours, COUNT(date) as attendanceDays, SUM(salary) as totalSalary')
                ->first();
            // 総勤務時間の計算
            $workMinutes = $summary->totalWorkHours % 60;     // 分部分
            
            // 表示用
            $formattedWorkHours = sprintf('%02d時間%02d分', $summary->totalWorkHours, $workMinutes);
    
            $data[] = [
                'name' => $employee->name,
                'attendanceDays' => $summary->attendanceDays ?? 0,
                'totalWorkHours' => $formattedWorkHours ?? 0,
                'totalSalary' => $summary->totalSalary ?? 0,
            ];
        }
    
        // ヘッダー行を含むデータを準備
        $csvData = [];
        $csvData[] = ['名前', '出勤日数', '総労働時間', '総給与'];
    
        foreach ($data as $row) {
            $csvData[] = [
                $row['name'],
                $row['attendanceDays'],
                $row['totalWorkHours'],
                '¥' . number_format($row['totalSalary']),
            ];
        }
    
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
    public function showDetails($date, $employeeId, $companyId)
    {
        // 指定日の打刻データを取得
        $aditRecords = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->where('date', $date)
            ->orderBy('minutes', 'asc')
            ->get();
        $employee = Employee::find($employeeId);

        if (!$employee || $aditRecords->isEmpty()) {
            return redirect()->back()->with('error', '該当するデータが見つかりません。');
        }

        return view('attendanceDetails', [
            'date' => $date,
            'employee' => $employee,
            'aditRecords' => $aditRecords,
        ]);
    }

    public function showCalendar(Request $request)
    {
        $selectedDate = carbon::parse($request->input('date', now()->toDateString()));

        // 現在の月のフォーマット
        $currentMonth = $selectedDate->format('Y年 n月'); 
        return view('calendar', [
            'currentMonth' => $currentMonth,
            'selectedDate' => $selectedDate,
        ]);
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

            $attendanceData[] = [
                'employee' => $employee,
                'totalDailySalary' => $totalDailySalary,
                'totalDailyWorkHours' => $totalDailyWorkHours,
                'totalDailyBreakHours' => $totalDailyBreakHours,
            ];
        }

        return view('dailyAttendance', [
            'selectedDate' => $selectedDate->toDateString(),
            'attendanceData' => $attendanceData,
            'totalSalary' => $totalSalary,
        ]);
    }

}