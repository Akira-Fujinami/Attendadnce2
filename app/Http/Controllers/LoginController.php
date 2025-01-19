<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Employee;
use Hash;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        // if (Auth::attempt($request->only('email', 'password'))) {
        //     return redirect()->route('staff')->with('success', 'ログインに成功しました');
        // }

        // if (Auth::guard('employees')->attempt($request->only('email', 'password'))) {
        //     return redirect()->route('adit')->with('success', '従業員としてログインしました');
        // }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Usersテーブルのパスワードを確認
            $employee = Employee::Join('users', 'users.id', '=', 'employees.company_id')
            ->select('employees.*')
            ->where('users.email', $request->email)
            ->first();
            if (Hash::check($request->password, $user->password)) {
                Auth::login($user);
                return redirect()->route('staff')
                ->with('success', 'ログインに成功しました')
                ->cookie('email', $request->email, 43200);
            } elseif ($employee && Hash::check($request->password, $employee->password)) {
                Auth::guard('employees')->login($employee);
                return redirect()->route('adit')->with('success', '従業員としてログインしました')
                ->cookie('email', $request->email, 43200);
            } else {
                return back()->withErrors(['email' => 'メールアドレスまたはパスワードが間違っています']); 
            }
        }

        return back()->withErrors(['email' => 'メールアドレスまたはパスワードが間違っています']);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // ユーザーをログアウト
        $request->session()->invalidate(); // セッションを無効化
        $request->session()->regenerateToken(); // CSRFトークンを再生成

        return redirect('/login'); // ログイン画面にリダイレクト
    }

    public function resetPassword(Request $request)
    {
        // $validated = $request->validate([
        //     'email' => 'required|email|exists:employees,email', // メールが必須かつ従業員テーブルに存在することを確認
        //     'new_password' => 'required|string|min:3|confirmed', // 確認用フィールドを追加
        // ]);
    
        // メールアドレスで従業員を検索
        $employee = User::where('email', $request['mail'])->first();
        // dd($employee);
    
        if (!$employee) {
            return redirect()->back()->with('error', '従業員が見つかりませんでした。');
        }
    
        // 新しいパスワードをハッシュ化して保存
        $employee->password = bcrypt($request['new_password']);
        $employee->save();
    
        return redirect()->back()->with('success', 'パスワードが正常にリセットされました。');
    }    
}
