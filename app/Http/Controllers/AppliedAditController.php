<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Adit;
use App\Models\DailySummary;
use Carbon\Carbon;

class AppliedAditController extends Controller
{
    public function index(Request $request, $companyId)
    {
        $statusFilter = $request->input('status', 'pending');
        // dd($statusFilter);
        $pendingRecords = Adit::where('company_id', $companyId)
                            ->when($statusFilter, function ($query, $statusFilter) {
                                $query->where('status', $statusFilter);
                            })
                            ->with(['employee:id,name,hourly_wage,transportation_fee']) // 必要なカラムを指定
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->groupBy('date') // 日付ごとにグループ化
                            ->map(function ($records) {
                                return $records->map(function ($record) use ($records) {
                                    $previousApproved = Adit::where('employee_id', $record->employee_id)->where('date', $record->date)->where('status', 'approved')->where('adit_item', $record->adit_item)->where('id', $record->before_adit_id)->first();
                                    if ($record->deleted == 1) {
                                        $previous_time = $record->minutes;
                                        $current_time = '削除';
                                    } else {
                                        $previous_time = $previousApproved->minutes ?? 'なし';
                                        $current_time = $record->minutes;
                                    }
                        
                                    return [
                                        'date' => $record->date,
                                        'name' => $record->employee->name ?? '未設定',
                                        'hourly_wage' => $record->employee->hourly_wage ?? 0,
                                        'transportation_fee' => $record->employee->transportation_fee ?? 0,
                                        'id' => $record->employee->id,
                                        'adit_id' => $record->before_adit_id,
                                        'minutes' => $record->minutes,
                                        'adit_item' => $record->adit_item,
                                        'previous_time' => $previous_time,
                                        'current_time' => $current_time,
                                        'deleted' => $record->deleted,
                                    ];
                                });
                            })
                            ->toArray();
                            // dd($pendingRecords);
                        
    
        return view('appliedAdit', [
            'pendingRecords' => $pendingRecords,
            'currentStatus' => $statusFilter,
        ]);
    }
    

    public function approveAdit(Request $request)
    {
        $pendingAdits = Adit::where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('minutes', $request->minutes)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'pending')
            ->get();
            // dd($pendingAdits);

        $approvedAdits = Adit::where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'approved')
            ->where('id', $request->before_adit_id)
            ->when($request->adit_id, function ($query, $adit_id) {
                return $query->where('id', $adit_id);
            })
            ->get();
            // dd($approvedAdits);
        // 承認処理
        foreach ($pendingAdits as $pendingAdit) {
            $pendingAdit->update(['status' => 'approved']);
        }
        foreach ($approvedAdits as $approvedAdit) {
            $approvedAdit->update(['status' => 'rejected']);
        }

        $date = date('Y-m-d', strtotime($request->date));
        $dailySummary = DailySummary::firstOrCreate(
            [
                'company_id' => $request->company_id,
                'employee_id' => $request->employee_id,
                'date' => $date,
            ],
            [
                'company_id' => $request->company_id,
                'employee_id' => $request->employee_id,
                'date' => $date,
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0,
                'salary' => 0,
            ]
        );
        $aditExists = Adit::whereDate('date', $date)
        ->where('company_id', $request->company_id)
        ->where('employee_id', $request->employee_id)
        ->exists();
        if ($aditExists && !AditController::error($request->company_id, $request->employee_id, $date)) {
            $totalBreakHours = AditController::calculateBreakHours($request->company_id, $request->employee_id, $date);
            $totalWorkHours = AditController::calculateWorkHours($request->company_id, $request->employee_id, $date, $totalBreakHours);
            // 給与を計算
            $salary = AditController::calculateSalary($request->wage, $request->transportation, $totalWorkHours, $totalBreakHours);

            $dailySummary->update([
            'total_work_hours' => $totalWorkHours,
            'total_break_hours' => $totalBreakHours,
            'overtime_hours' => max($totalWorkHours - 8, 0), // 8時間以上の場合は残業
            'salary' => $salary, // 給与計算ロジック
            ]);
        }

        return back();
    }

    public function rejectAdit(Request $request)
    {
        $adit = Adit::where('company_id', $request->company_id)
        ->where('employee_id', $request->employee_id)
        ->where('minutes', $request->minutes)
        ->where('adit_item', $request->adit_item)
        ->where('status', 'pending')
        ->first();
        if ($adit['deleted'] == 1) {
            $adit->update([
                'status' => 'approved',
                'deleted' => 0,
            ]);
            $date = date('Y-m-d', strtotime($request->date));
            $dailySummary = DailySummary::firstOrCreate(
                [
                    'company_id' => $request->company_id,
                    'employee_id' => $request->employee_id,
                    'date' => $date,
                ],
                [
                    'company_id' => $request->company_id,
                    'employee_id' => $request->employee_id,
                    'date' => $date,
                    'total_work_hours' => 0,
                    'total_break_hours' => 0,
                    'overtime_hours' => 0,
                    'salary' => 0,
                ]
            );
            $aditExists = Adit::whereDate('date', $date)
            ->where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->exists();
            if ($aditExists && !AditController::error($request->company_id, $request->employee_id, $date)) {
                $totalBreakHours = AditController::calculateBreakHours($request->company_id, $request->employee_id, $date);
                $totalWorkHours = AditController::calculateWorkHours($request->company_id, $request->employee_id, $date, $totalBreakHours);
                // 給与を計算
                $salary = AditController::calculateSalary($request->wage, $request->transportation, $totalWorkHours, $totalBreakHours);
    
                $dailySummary->update([
                'total_work_hours' => $totalWorkHours,
                'total_break_hours' => $totalBreakHours,
                'overtime_hours' => max($totalWorkHours - 8, 0), // 8時間以上の場合は残業
                'salary' => $salary, // 給与計算ロジック
                ]);
            }
            return back();
        }
        // $adit = Adit::findOrFail($id);

        // 却下処理
        $adit->update(['status' => 'rejected']);

        return back()->with('success', '打刻が却下されました。');
    }

}