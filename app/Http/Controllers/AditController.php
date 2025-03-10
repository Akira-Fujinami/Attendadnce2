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
            ->where('deleted', 0)
            ->exists();
        $latestAdit = Adit::whereDate('date', $today)
            ->where('employee_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', 'approved')
            ->where('deleted', 0)
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
            ->get()
            ->groupBy('date');

        $pendingRecords = Adit::whereBetween('date', [$lastMonthStart, $yesterday])
            ->where('employee_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->get()
            ->groupBy('date');

        $errors = [];
        $pending = [];
        $rejected = [];
        // エラーに追加
        foreach ($aditRecords as $date => $records) {
            $errorExists = self::error($user->company_id, $user->id, $date);
            $pendingRecordExists = Adit::whereDate('date', $date)
            ->where('employee_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->exists();
            if ($errorExists) {
                $errors[] = [
                    'date' => $date,
                    'error' => self::error($user->company_id, $user->id, $date),
                    'pending' => $pendingRecordExists,
                ];
            }
            if ($pendingRecordExists) {
                $pending[] = [
                    'date' => $date,
                    'pending' => 1,
                ];
            }
            $aditTypes = [
                'work_start' => '出勤',
                'work_end' => '退勤',
                'break_start' => '休憩開始',
                'break_end' => '休憩終了',
            ];
            
            $rejectedRecords = Adit::whereDate('date', $date)
            ->where('employee_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', 'rejected')
            ->where('confirm', 0)
            ->orderBy('created_at', 'desc') // 最新のレコードを取得しやすいようにソート
            ->get()
            ->groupBy('adit_item') // adit_itemごとにグループ化
            ->map(function ($records) {
                return $records->first(); // 各グループの最新のレコードを取得
            })
            ->values(); // 配列のキーをリセット
        
            
            if ($rejectedRecords->isNotEmpty()) {
                $rejected[] = [
                    'date' => $date,
                    'records' => $rejectedRecords->map(function ($record) use ($aditTypes) {
                        return [
                            'time' => \Carbon\Carbon::parse($record->minutes)->format('H:i'),
                            'type' => $aditTypes[$record->adit_item] ?? $record->adit_item, // 日本語に変換
                        ];
                    })->toArray(),
                ];
            }
            
        }

        // dd($latestAdit);
        $data = [
            'name' => $user->name,
            'status' => $status,
            'latestAdit' => $latestAdit ? $latestAdit->adit_item : null,
            'aditExists' => $aditExists,
            'errors' => $errors,
            'pending' => $pending,
            'rejected' => $rejected,
        ];

        return view('adit', compact('data'));
    }
    public function adit(Request $request) {
        Adit::create([
            'company_id' => $request->company_id,
            'employee_id' => $request->employee_id,
            'event_id' => $request->event,
            'date' => now()->format('Y-m-d'),
            'minutes' => now()->format('Y-m-d H:i'),
            'adit_item' => $request->adit_item,
            'status' => 'approved',
        ]);
        DailySummaryController::summary($request->company_id, $request->employee_id, $request->event, now()->format('Y-m-d'));
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
            ->where('company_id', $companyId)
            ->where('adit_item', 'break_start')
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->where('deleted', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    
        // 休憩終了データを取得
        $breakEnds = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
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
        // 勤務時間が0以下の場合は給与を0とする
        if ($totalWorkHours <= 0) {
            return 0;
        }
    
        // 通常の給与を計算
        $regularPay = $totalWorkHours * $wage;
    
        // 総給与を計算（交通費を加算）
        $salary = $regularPay + $transportation;
    
        return $salary;
    }

    public static function error($companyId, $employeeId, $date) {
        if (Carbon::parse($date)->gte(Carbon::today())) {
            return null;
        }
        // 指定された日付の打刻データを取得
        $records = Adit::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('status', '=', 'approved')
            ->where('deleted', 0)
            ->orderBy('minutes') // 時刻順にソート
            ->get(['adit_item', 'minutes']); // 必要なカラムのみ取得
    
        // データがない場合はエラーなし
        if ($records->isEmpty()) {
            return null;
        }
    
        // 各打刻を分類
        $workStart = null;
        $workEnd = null;
        $breaks = [];
        $lastEvent = null; // 直前のイベント
    
        foreach ($records as $record) {
            $time = \Carbon\Carbon::parse($record->minutes);
    
            if ($record->adit_item === 'work_start') {
                if ($workStart) return 1; // 出勤が複数回あるのはエラー
                if ($lastEvent !== null) return 1; // 出勤が最初でないのはエラー
                $workStart = $time;
                $lastEvent = 'work_start';
            } elseif ($record->adit_item === 'work_end') {
                if (!$workStart) return 1; // 出勤前に退勤があるのはエラー
                if ($workEnd) return 1; // 退勤が複数回あるのはエラー
                if ($lastEvent !== 'break_end' && $lastEvent !== 'work_start') return 1; // 直前が休憩終了か出勤でないならエラー
                $workEnd = $time;
                $lastEvent = 'work_end';
            } elseif ($record->adit_item === 'break_start') {
                if (!$workStart) return 1; // 出勤前に休憩開始はエラー
                if ($lastEvent === 'break_start') return 1; // 休憩開始が2回連続はエラー
                $breaks[] = ['start' => $time, 'end' => null]; // 休憩開始
                $lastEvent = 'break_start';
            } elseif ($record->adit_item === 'break_end') {
                if (!$workStart) return 1; // 出勤前に休憩終了はエラー
                if ($lastEvent !== 'break_start') return 1; // 直前が休憩開始でないならエラー
                // 直前の未終了の休憩を探して終了時間をセット
                foreach ($breaks as &$break) {
                    if ($break['end'] === null) {
                        $break['end'] = $time;
                        break;
                    }
                }
                $lastEvent = 'break_end';
            }
        }
    
        // 最後の打刻が退勤でないとエラー
        if ($lastEvent !== 'work_end') {
            return 1;
        }
    
        return null; // エラーなし
    }
    
    public function confirmAdit(Request $request)
    {
        try {
            // ログ出力でリクエストデータを確認
            \Log::info("確認済みリクエスト: ", $request->all());
    
            // レコードを取得
            $aditRecords = Adit::where('date', $request->date)
                ->where('employee_id', $request->employeeId)
                ->where('company_id', Auth::User()->company_id)
                ->where('status', 'rejected')
                ->get();
    
            if ($aditRecords->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'データが見つかりません'], 404);
            }
    
            // レコードの更新
            Adit::where('date', $request->date)
                ->where('employee_id', $request->employeeId)
                ->where('company_id', Auth::User()->company_id)
                ->where('status', 'rejected')
                ->update(['confirm' => true]);
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error("エラー発生: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    

}
