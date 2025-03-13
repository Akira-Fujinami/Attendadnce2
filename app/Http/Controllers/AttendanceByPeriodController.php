<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailySummary;
use App\Models\Employee;
use Carbon\Carbon;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AttendanceByPeriodController extends Controller
{
    /**
     * 期間別の出勤簿を表示
     */
    public function showByPeriod(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // 初回アクセス時は空の画面を表示
        if (!$start_date || !$end_date) {
            return view('attendanceByPeriod');
        }

        // 出勤データを取得
        $attendances = DailySummary::join('employees', 'daily_summaries.employee_id', '=', 'employees.id')
        ->select(
            'daily_summaries.employee_id as employeeId',
            'employees.name as employee_name',
            \DB::raw('COUNT(DISTINCT daily_summaries.date) as attendance_days'),
            \DB::raw('SUM(daily_summaries.total_work_hours) as work_minutes'),
            \DB::raw('SUM(daily_summaries.total_break_hours) as break_minutes'),
            \DB::raw('SUM(daily_summaries.salary) as salary')
        )
        ->where('daily_summaries.company_id', Auth()->user()->id)
        ->whereBetween('daily_summaries.date', [$start_date, $end_date])
        ->groupBy('daily_summaries.employee_id', 'employees.name')
        ->get();
        // dd($attendances);
        return view('attendanceByPeriod', [
            'attendances' => $attendances,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    /**
     * 期間別の出勤簿をエクスポート
     */
    public function exportPeriodAttendance(Request $request)
    {
        $employees = Employee::join('daily_summaries', 'employees.id', '=', 'daily_summaries.employee_id')
                    ->where('employees.company_id', $request->companyId)
                    ->whereBetween('date', [$request->startDate, $request->endDate])
                    ->select('employees.*')
                    ->distinct() // 重複を防ぐ
                    ->orderBy('employees.id')
                    ->get();

        $data = [];
        $totalSalary = 0;
    
        foreach ($employees as $employee) {
            $summary = DailySummary::where('company_id', $request->companyId)
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$request->startDate, $request->endDate])
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
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        $carbonStartDate = Carbon::parse($request->startDate);
        $formattedStartDate = $carbonStartDate->format('Y/m/d') . ' (' . $weekdays[$carbonStartDate->dayOfWeek] . ')';
        $carbonEndDate = Carbon::parse($request->endDate);
        $formattedEndDate = $carbonEndDate->format('Y/m/d') . ' (' . $weekdays[$carbonEndDate->dayOfWeek] . ')';
        $csvData[] = ["{$formattedStartDate}～{$formattedEndDate}の出勤簿"];

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

}
