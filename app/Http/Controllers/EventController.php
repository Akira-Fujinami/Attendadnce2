<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Adit;
use App\Models\Employee;
use App\Models\DailySummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\DB;

class EventController extends Controller
{

    public function show(Request $request)
    {
        $eventId = $request->input('event_id');
        $event = null;
        $totalSalary = 0;
        // 全イベント一覧を取得
        $allEvents = Event::where('company_id', Auth::User()->id)->get();
    
        // 選択されたイベントを取得
        if ($eventId) {
            $event = Event::where('company_id', Auth::User()->id)
                            ->where('id', $eventId)->first();
            $employees = Employee::where('company_id', Auth::User()->id)->get();
            $filteredEmployees = [];
            $totalSalary = 0;
            foreach ($employees as $employee) {
                $summary = DailySummary::where('company_id', Auth::User()->id)
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$event->fromDate, $event->toDate])
                ->selectRaw('SUM(total_work_hours) as totalWorkHours, COUNT(date) as attendanceDays, SUM(salary) as totalSalary')
                ->first();
                $attendanceDays = $summary->attendanceDays ?? 0;
                $totalWorkHours = $summary->totalWorkHours ?? 0;
                $totalSalaryForEmployee = $summary->totalSalary ?? 0;
    
                // いずれかの値が 0 より大きい場合のみ表示対象にする
                if ($attendanceDays > 0 || $totalWorkHours > 0 || $totalSalaryForEmployee > 0) {
                    $employee->attendanceDays = $attendanceDays;
                    $employee->totalWorkHours = $totalWorkHours;
                    $employee->totalSalary = $totalSalaryForEmployee;
                    $filteredEmployees[] = $employee;
    
                    // 総給与を加算
                    $totalSalary += $totalSalaryForEmployee;
                }
            }
            return view('eventAttendance', [
                'employees' => $filteredEmployees,
                'event' => $event,
                'allEvents' => $allEvents,
                'totalSalary' => $totalSalary,
            ]);
        }
    
    
        return view('eventAttendance', [
            'event' => $event,
            'allEvents' => $allEvents,
        ]);
    }
    
    public function create() {

        return view('event');
    }

    public function store(Request $request) {
        // バリデーションルール
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
        ], [
            // フィールドごとのエラーメッセージ
            'name.required' => 'イベント名を入力してください。',
            'name.string' => 'イベント名は文字列で入力してください。',
            'name.max' => 'イベント名は255文字以内で入力してください。',
    
            'start_date.required' => '開始日付を入力してください。',
            'start_date.date' => '開始日付は有効な日付である必要があります。',
    
            'end_date.required' => '終了日付を入力してください。',
            'end_date.date' => '終了日付は有効な日付である必要があります。',
            'end_date.after_or_equal' => '終了日付は開始日付以降の日付を指定してください。',
    
            'description.string' => '説明は文字列で入力してください。',
            'description.max' => '説明は1000文字以内で入力してください。',
        ]);

        // データ保存
        Event::insert([
            'company_id' => Auth::User()->id,
            'name' => $request->input('name'),
            'fromDate' => $request->input('start_date'),
            'toDate' => $request->input('end_date'),
            'description' => $request->input('description'),
            'created_at' => now(),
        ]);
    

        // 保存後、リダイレクトと成功メッセージを表示
        return redirect()->route('events.show')->with('success', 'イベントが保存されました！');
    }
    public function index() {
        $events = Event::where('company_id', Auth::User()->id)->get();
        return view('eventList', compact('events'));
    }
    public function delete($id) {
        $event = Event::findOrFail($id);
        $event->delete();
        return back();
    }

    public function export(Request $request)
    {
        $event = Event::findorfail($request->event_id);
        // データ取得（同じロジックを再利用）
        $employees = Employee::where('company_id', $request->company_id)->get();
        $data = [];
        $totalSalary = 0;
    
        foreach ($employees as $employee) {
            $summary = DailySummary::where('company_id', $request->company_id)
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$event->fromDate, $event->toDate])
                ->selectRaw('SUM(total_work_hours) as totalWorkHours, COUNT(date) as attendanceDays, SUM(salary) as totalSalary')
                ->first();
            // 総勤務時間の計算
            $workMinutes = $summary->totalWorkHours % 60;     // 分部分
            
            // 表示用
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