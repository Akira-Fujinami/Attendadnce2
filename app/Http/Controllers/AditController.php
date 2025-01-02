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
    
        $errorSummaries = DailySummary::where('company_id', $user->company_id)
        ->where('employee_id', $user->id)
        ->whereNotNull('error_types') // エラーがあるデータのみ取得
        ->get();

        // エラーの内容を配列に格納
        $errors = $errorSummaries->map(function ($summary) {
            return [
                'date' => $summary->date,
                'error' => is_array($summary->error_types)
                    ? implode(', ', $summary->error_types)
                    : $summary->error_types,
            ];
        })->toArray();
        if ($latestAdit && $latestAdit->status === 'pending') {
            $errors[] = [
                'date' => $latestAdit->date,
                'error' => '未承認の打刻があります',
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
        $aditRecords = Adit::where('company_id', $request->company_id)
                            ->where('employee_id', $request->employee_id)
                            ->where('date', $today)
                            ->orderBy('minutes')
                            ->pluck('adit_item');
    
        $expectedOrder = ['work_start', 'break_start', 'break_end', 'work_end'];
        $currentOrder = $aditRecords->toArray();
        
        $today = now()->format('Y-m-d');
        $aditExists = Adit::whereDate('created_at', $today)
                        ->where('company_id', $request->company_id)
                        ->where('employee_id', $request->employee_id)
                        ->exists();
        if ($aditExists) {
            $totalWorkHours = $this->calculateWorkHours($request->company_id, $request->employee_id, $today);
            $totalBreakHours = $this->calculateBreakHours($request->company_id, $request->employee_id, $today);
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

    protected function calculateWorkHours($companyId, $employeeId, $date)
    {
        $workStart = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->where('adit_item', 'work_start')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->first();
        // dd($workStart);

        $workEnd = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->where('adit_item', 'work_end')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->first();
            // dd($workEnd);

        if ($workStart && $workEnd) {
            $totalWorkHours = \Carbon\Carbon::parse($workStart->minutes)->diffInMinutes(\Carbon\Carbon::parse($workEnd->minutes));
            $hours = floor($totalWorkHours / 60);
                        
            $minutes = $totalWorkHours % 60;
            return ($hours + ($minutes / 100));
        }

        return 0;
    }

    protected function calculateBreakHours($companyId, $employeeId, $date)
    {
        $breakStart = Adit::where('employee_id', $employeeId)
            ->where('adit_item', 'break_start')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->first();

        $breakEnd = Adit::where('employee_id', $employeeId)
            ->where('adit_item', 'break_end')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->first();
            // dd($breakEnd);

        if ($breakStart && $breakEnd) {
            $breakStartTime = Carbon::parse($breakStart->minutes); // Carbonインスタンスに変換
            $breakEndTime = Carbon::parse($breakEnd->minutes); // Carbonインスタンスに変換
            return $breakStartTime->diffInHours($breakEndTime); // 休憩時間を計算
        }

        return 0;
    }

    protected function calculateSalary($wage, $transportation, $totalWorkHours, $totalBreakHours)
    {
        // 実働時間を計算
        $actualWorkHours = $totalWorkHours - $totalBreakHours;

        // 給与を計算
        $salary = 0;
        if ($actualWorkHours > 0) {
            $salary = ($actualWorkHours * $wage) + $transportation;
        }

        return $salary;
    }

}
