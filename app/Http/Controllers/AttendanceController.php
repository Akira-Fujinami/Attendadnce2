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
                            ->whereIn('status', ['approved', 'pending'])
                            ->where('deleted', 0)
                            ->orderBy('created_at', 'desc') // 最新の順に並べる
                            ->get()
                            ->groupBy('date') // 日付ごとにグループ化
                            ->map(function ($records, $date) {
                                // `$records` の中に pending ステータスがあるかを確認
                                $hasPending = $records->contains(function ($record) {
                                    return $record->status === 'pending';
                                });
                        
                                // エラーフラグを追加
                                $error = AditController::error(Auth::User()->company_id, Auth::User()->id, $date);
                                $sum = DailySummary::where('employee_id', Auth::User()->id)
                                    ->where('company_id', Auth::User()->company_id)
                                    ->where('date', $date)
                                    ->first();
                                    // dd($Sum);

                                $totalBreakHours = $sum->total_break_hours ?? 0; // デフォルト値を設定
                                $totalWorkHours = $sum->total_work_hours ?? 0;   // デフォルト値を設定
                                $mappedRecords = $records->map(function ($record) use ($hasPending) {
                                    return [
                                        'id' => $record->id,
                                        'date' => $record->date,
                                        'status' => $record->status,
                                        'minutes' => ($record->status === 'approved' || !$hasPending) ? $record->minutes : null,
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
            'event' => $request->event_id,
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
        $employeeIds = Employee::join('daily_summaries', 'employees.id', '=', 'daily_summaries.employee_id')
                    ->where('employees.company_id', $request->companyId)
                    ->whereBetween('date', [$startDate, $endDate]) 
                    ->where('retired', '在職中')
                    ->groupBy('employees.id')
                    ->pluck('employees.id');
        $employees = Employee::whereIn('id', $employeeIds)->get();
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
            ->where('company_id', Auth::User()->id)
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
        $name = Employee::where('id', Auth::User()->id)->where('company_id', Auth::User()->company_id)->first()->name;
        // 日付が未来または明日かどうかを確認
        if (Carbon::parse($date)->isFuture() || Carbon::parse($date)->isTomorrow()) {
            return view('editAttendance', [
                'disable' => 1,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'name' => $name,
            ]);
        }
        $recruit = Employee::where('id', Auth::User()->id)
                    ->where('company_id', Auth::User()->company_id)
                    ->first();

        if ($recruit && Carbon::parse($recruit->created_at)->toDateString() > Carbon::parse($date)->toDateString()) {
            return view('editAttendance', [
                'disable' => 1,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'name' => $name,
            ]);
        }


        // 該当する従業員とその日付の打刻データを取得
        $records = Adit::where('employee_id', $employeeId)
            ->where('company_id', Auth::User()->company_id)
            ->whereDate('date', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->orderBy('created_at', 'asc') // 古い順にソート
            ->get();
        $eventId = $records->pluck('event_id')->first() ?? '';
        $eventSelected = Event::where('id', $eventId)
        ->first();

        $pendingRecords = $records->contains(function ($record) {
                return $record->status === 'pending' && $record->deleted === 0;
            });

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
        $events = Event::where('fromDate', '<=', $date)
               ->where('toDate', '>=', $date)
               ->get();


        // Bladeに渡すデータ
        return view('editAttendance', [
            'disable' => 0,
            'date' => $date,
            'name' => $name,
            'employeeId' => $employeeId,
            'records' => $data,
            'pending' => $pendingRecords,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'events' => $events,
            'eventSelected' => $eventSelected,
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
        $exists = Adit::where('employee_id', $request->employeeId)
        ->where('company_id', $request->companyId)
        ->where('minutes', $request->minutes)
        ->where('adit_item', $request->adit_item)
        ->where('status', 'pending')
        ->where('deleted', 1)
        ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['minutes' => '削除待ちの打刻は操作できません。'])
                ->withInput();
        }
    
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
                        'event_id' => $request->event,
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
                    'event_id' => $request->event,
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
        $currentMonth = $request->input('month', now()->month);
        $currentYear = $request->input('year', now()->year);
        
        // 指定された月の開始日と終了日を取得
        $startDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->toDateString();

        $employees = Employee::join('daily_summaries', 'employees.id', '=', 'daily_summaries.employee_id')
                    ->where('employees.company_id', $request->companyId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->select('employees.*')
                    ->distinct() // 重複を防ぐ
                    ->get();

        $data = [];
        $totalSalary = 0;
    
        foreach ($employees as $employee) {
            $summary = DailySummary::where('company_id', $request->companyId)
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('SUM(total_work_hours) as totalWorkHours, COUNT(date) as attendanceDays, SUM(salary) as totalSalary')
                ->first();
            // 総勤務時間の計算
            $totalHoursDecimal = $summary->totalWorkHours ?? 0; // 例: 8.12
            $hours = floor($totalHoursDecimal); // 時間部分 (整数)
            $minutes = round(($totalHoursDecimal - $hours) * 60); // 分部分 (小数を60進数に変換)
        
            // 表示用フォーマット
            $formattedWorkHours = sprintf('%02d時間%02d分', $hours, $minutes);
            $salary = $summary->totalSalary ?? 0;
            $totalSalary += $salary;
    
            $data[] = [
                'name' => $employee->name,
                'attendanceDays' => $summary->attendanceDays ?? 0,
                'totalWorkHours' => $formattedWorkHours ?? 0,
                'totalSalary' => $summary->totalSalary ?? 0,
            ];
        }
    
        // ヘッダー行を含むデータを準備
        $csvData = [];

        // **先頭に「〇〇年〇〇月分の出勤簿」を追加**
        $csvData[] = ["{$currentYear}年 {$currentMonth}月分の出勤簿"];

        // 空行を追加（見やすくするため）
        $csvData[] = [];

        $csvData[] = ['名前', '出勤日数', '総労働時間', '総給与'];
    
        foreach ($data as $row) {
            $csvData[] = [
                $row['name'],
                $row['attendanceDays'],
                $row['totalWorkHours'],
                '¥' . number_format($row['totalSalary']),
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
}