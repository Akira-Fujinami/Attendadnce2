<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Adit;
use App\Models\Employee;
use App\Models\DailySummary;
use Carbon\Carbon;

class DailySummaryController extends Controller
{
    public static function summary($companyId, $employeeId, $eventId, $date) {
        $date = date('Y-m-d', strtotime($date));
        $dailySummary = DailySummary::firstOrCreate(
            [
                'company_id' => $companyId,
                'employee_id' => $employeeId,
                'date' => $date,
            ],
            [
                'company_id' => $companyId,
                'employee_id' => $employeeId,
                'event_id' => $eventId,
                'date' => $date,
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0,
                'salary' => 0,
            ]
        );
        $aditExists = Adit::whereDate('date', $date)
        ->where('company_id', $companyId)
        ->where('employee_id', $employeeId)
        ->where('deleted', 0)
        ->exists();
        $employee = Employee::find($employeeId);
        if ($aditExists && !AditController::error($companyId, $employeeId, $date)) {
            $totalBreakHours = AditController::calculateBreakHours($companyId, $employeeId, $date);
            $totalWorkHours = AditController::calculateWorkHours($companyId, $employeeId, $date, $totalBreakHours);
            // 給与を計算
            $salary = AditController::calculateSalary($employee->hourly_wage, $employee->transportation_fee, $totalWorkHours, $totalBreakHours);

            $dailySummary->update([
            'event_id' => $eventId,
            'total_work_hours' => $totalWorkHours,
            'total_break_hours' => $totalBreakHours,
            'overtime_hours' => max($totalWorkHours - 8, 0), // 8時間以上の場合は残業
            'salary' => $salary, // 給与計算ロジック
            ]);
        }
        if (AditController::error($companyId, $employeeId, $date)) {
            $dailySummary->update([
                'event_id' => $eventId,
                'total_work_hours' => 0,
                'total_break_hours' => 0,
                'overtime_hours' => 0, // 8時間以上の場合は残業
                'salary' => 0, // 給与計算ロジック
                ]);
        }
    }
}