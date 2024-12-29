<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Adit;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request) {
        $employeeId = $request->employee_id;
        $companyId = $request->company_id;

        // 12月1日から31日までの日付を生成
        $dates = [];
        $now = Carbon::now('Asia/Tokyo');

        // 月初と月末を設定
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        // データベースから打刻情報を取得
        $attendanceRecords = Adit::where('employee_id', $employeeId)
            ->where('company_id', $companyId)
            ->whereBetween('date', [reset($dates), end($dates)])
            ->get()
            ->keyBy('date'); // 日付をキーにして取得

        return view('attendance', [
            'dates' => $dates,
            'attendanceRecords' => $attendanceRecords,
        ]);
    }
}