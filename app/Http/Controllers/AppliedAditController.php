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
                            ->orderBy('date', 'asc')
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
                                        'event_id' => $record->event_id,
                                        'previous_time' => $previous_time,
                                        'current_time' => $current_time,
                                        'deleted' => $record->deleted,
                                    ];
                                });
                            })
                            ->toArray();
                        
    
        return view('appliedAdit', [
            'pendingRecords' => $pendingRecords,
            'currentStatus' => $statusFilter,
        ]);
    }
    

    public function approveAdit(Request $request)
    {
        $pendingAdit = Adit::where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('minutes', $request->minutes)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'pending')
            ->first();

        $approvedAdit = Adit::where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'approved')
            ->where('id', $request->before_adit_id)
            ->when($request->adit_id, function ($query, $adit_id) {
                return $query->where('id', $adit_id);
            })
            ->first();
        $rejectAdit = Adit::where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('minutes', $request->minutes)
            ->where('adit_item', $request->adit_item)
            ->where('status', 'rejected')
            ->first();
        $multiAdits = Adit::where('company_id', $request->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('adit_item', $request->adit_item)
            ->whereIn('status', ['approved', 'pending'])
            ->get();
        // TODO dateでフィルターしている為、休憩打刻が複数回されてる場合は不具合の可能性あり
        if ($rejectAdit) {
            $rejectAdit->update(['status' => 'approved']);
            foreach ($multiAdits as $multiAdit) {
                if ($multiAdit['adit_item'] == 'work_start' || $multiAdit['adit_item'] == 'work_end') {
                    $multiAdit->update(['status' => 'rejected']);
                }
            }
        }
        // 承認処理
        if ($pendingAdit) {
            $pendingAdit->update(['status' => 'approved']);
        }
        if ($approvedAdit) {
            $approvedAdit->update(['status' => 'rejected']);
        }

        DailySummaryController::summary($request->company_id, $request->employee_id, $request->event_id, $request->date);

        return redirect()->route('appliedAdit', ['companyId' => Auth::user()->id]);
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
                'event_id' => $request->event_id,
                'status' => 'approved',
                'deleted' => 0,
            ]);
            DailySummaryController::summary($request->company_id, $request->employee_id, $request->event_id, $request->date);
            return back();
        }

        // 却下処理
        $adit->update(['status' => 'rejected']);

        return back()->with('success', '打刻が却下されました。');
    }

}