<?php

namespace App\Http\Controllers;

use App\Models\Adit;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\DailySummary;
use Carbon\Carbon;
use Auth;

class AttendanceDetailsController extends Controller
{
    public function showDetails($date, $employeeId, $companyId)
    {
        // 指定日の打刻データを取得
        $aditRecords = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->where('date', $date)
            ->where('deleted', 0)
            ->orderBy('minutes', 'asc')
            ->get();
        $employee = Employee::find($employeeId);

        if (!$employee || $aditRecords->isEmpty()) {
            return redirect()->back()->with('error', '該当するデータが見つかりません。');
        }

        return view('attendanceDetails', [
            'date' => $date,
            'employee' => $employee,
            'aditRecords' => $aditRecords,
        ]);
    }
    public function update(Request $request, $id) {
        $adit = Adit::findOrFail($id);
        $newDatetime = Carbon::parse($adit->date . ' ' . $request->input('minutes'))->format('Y-m-d H:i:s');

        // 更新
        $adit->minutes = $newDatetime;
        $adit->adit_item = $request->input('adit_item');
        $adit->status = 'approved';
        $adit->save();
        DailySummaryController::summary($adit->company_id, $adit->employee_id, $adit->date);
        return redirect()->back()->with('success', '打刻が更新されました');
    }
    public function delete(Request $request, $id) {
        $adit = Adit::findOrFail($id);
        $adit->status = 'approved';
        $adit->deleted = 1;
        $adit->save();
        DailySummaryController::summary($adit->company_id, $adit->employee_id, $adit->date);
        return redirect()->back()->with('success', '打刻が更新されました');
    }

    public function store(Request $request) {
        $validatedData = $request->validate([
            'minutes' => 'required|date_format:H:i',
            'adit_item' => 'required|in:work_start,break_start,break_end,work_end',
        ]);
        $newDatetime = Carbon::parse($request->date . ' ' . $request->input('minutes'))->format('Y-m-d H:i:s');

        $adit = Adit::create([
            'company_id' => $request->company,
            'employee_id' => $request->employee,
            'date' => $request->date,
            'minutes' => $newDatetime,
            'adit_item' => $request->input('adit_item'),
            'status' => 'approved',
        ]);
        DailySummaryController::summary($adit->company_id, $adit->employee_id, $adit->date);
        return redirect()->back()->with('success', '打刻が追加されました');
    }


}