<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\DailySummary;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', '在職中');
        $user = Auth::User();
        $query = Employee::query();

        // ステータスでフィルタリング
        if ($status !== 'すべて') {
            $query->where('retired', $status);
        }
    
        // 従業員リストを取得
        $EmployeeList = $query->where('company_id', Auth::user()->id)->get();
        foreach ($EmployeeList as $employee) {
            $errors = DailySummary::where('company_id', $user->id)
                ->where('employee_id', $employee->id)
                ->whereNotNull('error_types')
                ->with('employee')
                ->get()
                ->pluck('error_types', 'employee.name') // 名前をキーにする
                ->toArray();
            $employee->errors = $errors;
        }

        return view('staff', [
            'EmployeeList' => $EmployeeList,
            'currentStatus' => $status,
        ]);
    }
    public function detail(Request $request)
    {
        // 指定された社員IDを取得
        $employeeId = $request->employeeId;
    
        // 該当する社員情報を取得
        $employee = Employee::find($employeeId);
    
        if (!$employee) {
            // 該当する社員が見つからなければ404エラーを返す
            abort(404, 'スタッフが見つかりません。');
        }
    
        // Blade にデータを渡して表示
        return view('staffDetail', ['employee' => $employee]);
    }
    public function edit(Request $request)
    {
        // 指定された社員IDを取得
        $employeeId = $request->employeeId;
    
        // 該当する社員情報を取得
        $employee = Employee::find($employeeId);
    
        if (!$employee) {
            // 該当する社員が見つからなければ404エラーを返す
            abort(404, 'スタッフが見つかりません。');
        }
    
        // Blade にデータを渡して表示
        return view('staffEdit', ['employee' => $employee]);
    }
    
    public function create(Request $request) {
        Employee::create([
            'company_id' => Auth::User()->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff',
            'hourly_wage' => $request->wage,
            'transportation_fee' => $request->transportation,
            'retired' => $request->status,
        ]);
        return redirect()->route('staff');
    }

    public function update(Request $request, $id)
    {
        // 入力値のバリデーション
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $id,
            'password' => 'nullable|string|min:3',
            'transportation_fee' => 'required|numeric',
            'hourly_wage' => 'required|numeric',
            'retired' => 'required|string|in:在職中,退職済み',
        ]);
    
        // 更新対象の従業員レコードを取得
        $employee = Employee::find($id);
    
        if (!$employee) {
            return redirect()->route('staff')->with('error', 'スタッフが見つかりませんでした。');
        }
    
        // データを更新
        $employee->name = $validatedData['name'];
        $employee->email = $validatedData['email'];
        if (!empty($validatedData['password'])) {
            $employee->password = bcrypt($validatedData['password']); // パスワードをハッシュ化
        }
        $employee->transportation_fee = $validatedData['transportation_fee'];
        $employee->hourly_wage = $validatedData['hourly_wage'];
        $employee->retired = $validatedData['retired'];
    
        // 保存
        $employee->save();
        $employee = Employee::find($id);
    
        // 成功メッセージを追加してリダイレクト
        return redirect()->route('staff');
    }
    
}
