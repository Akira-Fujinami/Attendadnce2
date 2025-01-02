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
        $pendingRecords = Adit::where('company_id', $companyId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('date') // 日付ごとにグループ化
            ->map(function ($records) {
                return $records->map(function ($record) use ($records) {
                    $previousApproved = $records->where('status', 'approved')->where('adit_item', $record->adit_item)->last();
    
                    return [
                        'date' => $record->date,
                        'name' => $record->employee->name ?? '未設定',
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
        // $adit = Adit::findOrFail($id);

        // 承認処理
        foreach ($adits as $adit) {
            $adit->update(['status' => 'approved']);
        }

        return redirect()->route('appliedAdit', ['company_id' => $request->company_id]);
    }

    public function rejectAdit(Request $request)
    {
        $adit = Adit::findOrFail($id);

        // 却下処理
        $adit->update(['status' => 'rejected']);

        return back()->with('success', '打刻が却下されました。');
    }

}