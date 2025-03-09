<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Event;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Adit;
use App\Models\DailySummary;

class AditQrController extends Controller
{
    // 打刻画面を表示する
    public function index($eventId)
    {
        $user = Auth::guard('employees')->user();

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
            $errorExists = AditController::error($user->company_id, $user->id, $date);
            $pendingRecordExists = Adit::whereDate('date', $date)
            ->where('employee_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->exists();
            if ($errorExists) {
                $errors[] = [
                    'date' => $date,
                    'error' => AditController::error($user->company_id, $user->id, $date),
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
            'event' => Event::findOrFail($eventId),
        ];


        return view('adit', compact('data'));
    }
}
