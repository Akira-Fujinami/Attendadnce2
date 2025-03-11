<?php

namespace App\Http\Controllers;

use App\Models\Adit;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\DailySummary;
use App\Models\Event;
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
            ->where('status', '!=', 'rejected')
            ->orderBy('minutes', 'asc')
            ->get();
        $eventId = $aditRecords->pluck('event_id')->first() ?? '';
        $eventSelected = Event::where('id', $eventId)
            ->first();
        $employee = Employee::find($employeeId);

        if (!$employee || $aditRecords->isEmpty()) {
            return redirect()->back()->with('error', '該当するデータが見つかりません。');
        }

        return view('attendanceDetails', [
            'date' => $date,
            'employee' => $employee,
            'aditRecords' => $aditRecords,
            'eventSelected' => $eventSelected,
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
        $records = Adit::where('company_id', Auth::User()->id)
        ->where('employee_id', $request->employee)
        ->where('date', $request->date)
        ->whereIn('adit_item', ['work_start', 'work_end'])
        ->where('status', 'approved')
        ->where('deleted', 0)
        ->pluck('adit_item') // `adit_item` のみ取得
        ->toArray();

        // 既に同じ打刻が存在する場合、バリデーションエラーを返す
        if (in_array($validatedData['adit_item'], $records)) {
            return redirect()->back()
                ->withErrors(['adit_item' => '既に打刻されています。'])
                ->withInput();
        }

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