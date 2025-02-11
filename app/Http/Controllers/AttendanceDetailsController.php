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
        $dailySummary = DailySummary::firstOrCreate(
            [
                'company_id' => $adit->company_id,
                'employee_id' => $adit->employee_id,
                'date' => $adit->date,
            ],
            [
                'company_id' => $adit->company_id,
                'employee_id' => $adit->employee_id,
                'date' => $adit->date,
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0,
                'salary' => 0,
            ]
        );
        $aditExists = Adit::whereDate('date', $adit->date)
        ->where('company_id', $adit->company_id)
        ->where('employee_id', $adit->employee_id)
        ->exists();
        $employee = Employee::find($adit->employee_id);
        if ($aditExists && !AditController::error($adit->company_id, $adit->employee_id, $adit->date)) {
            $totalBreakHours = AditController::calculateBreakHours($adit->company_id, $adit->employee_id, $adit->date);
            $totalWorkHours = AditController::calculateWorkHours($adit->company_id, $adit->employee_id, $adit->date, $totalBreakHours);
            // 給与を計算
            $salary = AditController::calculateSalary($employee->hourly_wage, $employee->transportation_fee, $totalWorkHours, $totalBreakHours);

            $dailySummary->update([
            'total_work_hours' => $totalWorkHours,
            'total_break_hours' => $totalBreakHours,
            'overtime_hours' => max($totalWorkHours - 8, 0), // 8時間以上の場合は残業
            'salary' => $salary, // 給与計算ロジック
            ]);
        }
        if (AditController::error($adit->company_id, $adit->employee_id, $adit->date)) {
            $dailySummary->update([
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0, // 8時間以上の場合は残業
                'salary' => 0, // 給与計算ロジック
                ]);
        }
        return redirect()->back()->with('success', '打刻が更新されました');
    }
    public function delete(Request $request, $id) {
        $adit = Adit::findOrFail($id);
        $adit->status = 'approved';
        $adit->deleted = 1;
        $adit->save();
        $dailySummary = DailySummary::firstOrCreate(
            [
                'company_id' => $adit->company_id,
                'employee_id' => $adit->employee_id,
                'date' => $adit->date,
            ],
            [
                'company_id' => $adit->company_id,
                'employee_id' => $adit->employee_id,
                'date' => $adit->date,
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0,
                'salary' => 0,
            ]
        );
        $aditExists = Adit::whereDate('date', $adit->date)
        ->where('company_id', $adit->company_id)
        ->where('employee_id', $adit->employee_id)
        ->exists();
        $employee = Employee::find($adit->employee_id);
        if ($aditExists && !AditController::error($adit->company_id, $adit->employee_id, $adit->date)) {
            $totalBreakHours = AditController::calculateBreakHours($adit->company_id, $adit->employee_id, $adit->date);
            $totalWorkHours = AditController::calculateWorkHours($adit->company_id, $adit->employee_id, $adit->date, $totalBreakHours);
            // 給与を計算
            $salary = AditController::calculateSalary($employee->hourly_wage, $employee->transportation_fee, $totalWorkHours, $totalBreakHours);

            $dailySummary->update([
            'total_work_hours' => $totalWorkHours,
            'total_break_hours' => $totalBreakHours,
            'overtime_hours' => max($totalWorkHours - 8, 0), // 8時間以上の場合は残業
            'salary' => $salary, // 給与計算ロジック
            ]);
        }
        if (AditController::error($adit->company_id, $adit->employee_id, $adit->date)) {
            $dailySummary->update([
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0, // 8時間以上の場合は残業
                'salary' => 0, // 給与計算ロジック
                ]);
        }
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

        $dailySummary = DailySummary::firstOrCreate(
            [
                'company_id' => $adit->company_id,
                'employee_id' => $adit->employee_id,
                'date' => $adit->date,
            ],
            [
                'company_id' => $adit->company_id,
                'employee_id' => $adit->employee_id,
                'date' => $adit->date,
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0,
                'salary' => 0,
            ]
        );
        $aditExists = Adit::whereDate('date', $adit->date)
        ->where('company_id', $adit->company_id)
        ->where('employee_id', $adit->employee_id)
        ->exists();
        $employee = Employee::find($adit->employee_id);
        if ($aditExists && !AditController::error($adit->company_id, $adit->employee_id, $adit->date)) {
            $totalBreakHours = AditController::calculateBreakHours($adit->company_id, $adit->employee_id, $adit->date);
            $totalWorkHours = AditController::calculateWorkHours($adit->company_id, $adit->employee_id, $adit->date, $totalBreakHours);
            // 給与を計算
            $salary = AditController::calculateSalary($employee->hourly_wage, $employee->transportation_fee, $totalWorkHours, $totalBreakHours);

            $dailySummary->update([
            'total_work_hours' => $totalWorkHours,
            'total_break_hours' => $totalBreakHours,
            'overtime_hours' => max($totalWorkHours - 8, 0), // 8時間以上の場合は残業
            'salary' => $salary, // 給与計算ロジック
            ]);
        }
        if (AditController::error($adit->company_id, $adit->employee_id, $adit->date)) {
            $dailySummary->update([
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0, // 8時間以上の場合は残業
                'salary' => 0, // 給与計算ロジック
                ]);
        }

        return redirect()->back()->with('success', '打刻が追加されました');
    }


}