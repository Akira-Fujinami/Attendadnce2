<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Adit;
use App\Models\DailySummary;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $EmployeeList = $query->where('company_id', Auth::user()->id)->get();
        $errors = []; // エラー配列を初期化
        foreach ($EmployeeList as $employee) {
            $pendingRecords = Adit::where('employee_id', $employee->id)
            ->where('company_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('date') // 日付ごとにグループ化
            ->map(function ($records, $date) {
                $latestRecord = $records->first(); // 最新の未承認打刻
                return [
                    'date' => $latestRecord->date,
                    'name' => $latestRecord->employee->name ?? '未設定',
                    'status' => $latestRecord->status,
                ];
            })
            ->values() // 配列として保持
            ->toArray();
            $employee->pendingRecords = $pendingRecords;

            $aditRecords = Adit::whereBetween('date', [$lastMonthStart, $yesterday])
            ->where('company_id', $user->id)
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'rejected')
            ->with('employee')
            ->get()
            ->groupBy('date');
        
            // 退勤打刻がない日付をチェック
            $missingWorkEndDates = collect();

            // 退勤打刻がない日付をエラーリストに追加
            foreach ($missingWorkEndDates as $missingDate) {
                $errors[$employee->name][] = '退勤打刻がありません (' . $missingDate . ')';
            }

            foreach ($aditRecords as $date => $records) {
                $errorExist = AditController::error($user->id, $employee->id, $date);
                if ($errorExist) {
                    $errors[$employee->name][] = '打刻が不正です (' . $date . ')';
                }
            }
        
            // プロパティに追加
            $employee->setAttribute('errors', $errors[$employee->name] ?? []);
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
        // 入力値のバリデーション
        $validatedData = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:employees,email,',
                'password' => 'nullable|string|min:3',
                'transportation' => 'required|numeric',
                'wage' => 'required|numeric',
                'retired' => 'required|string|in:在職中,退職済み',
            ],
            [
                'name.required' => '名前を入力してください。',
                'name.max' => '名前は255文字以内で入力してください。',
                'email.required' => 'メールアドレスを入力してください。',
                'email.email' => '有効なメールアドレスを入力してください。',
                'email.unique' => '入力されたメールアドレスは既に登録されています。',
                'password.min' => 'パスワードは最低3文字以上で入力してください。',
                'transportation.required' => '交通費を入力してください。',
                'transportation.numeric' => '交通費は数値で入力してください。',
                'wage.required' => '時給を入力してください。',
                'wage.numeric' => '時給は数値で入力してください。',
                'retired.required' => '在籍状況を選択してください。',
                'retired.in' => '在籍状況は「在職中」または「退職済み」を選択してください。',
            ]
        );
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
        $validatedData = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:employees,email,' . $id,
                'password' => 'nullable|string|min:3',
                'transportation_fee' => 'required|numeric',
                'hourly_wage' => 'required|numeric',
                'retired' => 'required|string|in:在職中,退職済み',
            ],
            [
                'name.required' => '名前を入力してください。',
                'name.max' => '名前は255文字以内で入力してください。',
                'email.required' => 'メールアドレスを入力してください。',
                'email.email' => '有効なメールアドレスを入力してください。',
                'email.unique' => '入力されたメールアドレスは既に登録されています。',
                'password.min' => 'パスワードは最低3文字以上で入力してください。',
                'transportation_fee.required' => '交通費を入力してください。',
                'transportation_fee.numeric' => '交通費は数値で入力してください。',
                'hourly_wage.required' => '時給を入力してください。',
                'hourly_wage.numeric' => '時給は数値で入力してください。',
                'retired.required' => '在籍状況を選択してください。',
                'retired.in' => '在籍状況は「在職中」または「退職済み」を選択してください。',
            ]
        );
        
    
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
