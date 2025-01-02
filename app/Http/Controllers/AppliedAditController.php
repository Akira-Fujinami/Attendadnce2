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
                                    $previousApproved = $records->where('status', 'approved')->where('adit_item', $record->adit_item)->last();
                        
                                    return [
                                        'date' => $record->date,
                                        'name' => $record->employee->name ?? '未設定',
                                        'hourly_wage' => $record->employee->hourly_wage ?? 0,
                                        'transportation_fee' => $record->employee->transportation_fee ?? 0,
                                        'id' => $record->employee->id,
                                        'minutes' => $record->minutes,
                                        'adit_item' => $record->adit_item,
                                        'previous_time' => $previousApproved->minutes ?? 'なし',
                                        'current_time' => $record->minutes,
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
        $adits = Adit::where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('minutes', $request->date)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'pending')
            ->get();

        // 承認処理
        foreach ($adits as $adit) {
            $adit->update(['status' => 'approved']);
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
                'error_types' => null,
            ]
        );
        $aditExists = Adit::whereDate('date', $date)
        ->where('company_id', $request->company_id)
        ->where('employee_id', $request->employee_id)
        ->exists();
        if ($aditExists) {
            $totalWorkHours = AditController::calculateWorkHours($request->company_id, $request->employee_id, $date);
            $totalBreakHours = AditController::calculateBreakHours($request->company_id, $request->employee_id, $date);
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
        $adits = Adit::where('company_id', $request->company_id)
        ->where('employee_id', $request->employee_id)
        ->where('minutes', $request->date)
        ->where('adit_item', $request->adit_item)
        ->where('status', 'pending')
        ->get();
        // $adit = Adit::findOrFail($id);

        // 却下処理
        foreach ($adits as $adit) {
            $adit->update(['status' => 'rejected']);
        }

        return back()->with('success', '打刻が却下されました。');
    }

}