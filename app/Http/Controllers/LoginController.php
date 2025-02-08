<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Employee;
use Hash;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        session()->flush();
        if ($user && Hash::check($request->password, $user->password)) {
            session(['last_login_email' => $request->email]);
            Auth::login($user);
            return redirect()->route('staff')
            ->withCookie(cookie('email', $request->email, 60 * 24 * 30))
            ->with('success', 'ログインに成功しました');
        }
        $employees = Employee::join('users', 'users.id', '=', 'employees.company_id')
        ->select('employees.*')
        ->where('users.email', $request->email)
        ->get()
        ->keyBy('id');
        $matchedEmployee = $employees->first(function ($employee) use ($request) {
            return Hash::check($request->password, $employee->password);
        });


        // 取得した従業員をループで確認
        if ($matchedEmployee) {
            // 該当する従業員が見つかった場合ログイン処理
            session(['lastActivityTime' => now()->timestamp]); // 最終アクティビティを記録
            session(['last_login_email' => $request->email]);
            Auth::guard('employees')->login($matchedEmployee);
            return redirect()->route('adit')
                ->withCookie(cookie('email', $request->email, 60 * 24 * 30))
                ->with('success', '従業員としてログインしました');
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
        $validated = $request->validate(
            [
                'mail' => 'required|email',
                'new_password' => 'required|string|min:3|confirmed',
            ],
            [
                'mail.required' => 'メールアドレスを入力してください。',
                'mail.email' => '有効なメールアドレスを入力してください。',
                'new_password.required' => '新しいパスワードを入力してください。',
                'new_password.min' => 'パスワードは最低3文字以上必要です。',
                'new_password.confirmed' => '確認用パスワードが一致しません。',
            ]
        );
    
        // メールアドレスで従業員を検索
        $admin = User::where('email', $request['mail'])->first();
        $employee = Employee::where('email', $request['mail'])->first();
    
        if (!$admin && !$employee) {
            return redirect()->back()->with('error', '入力されたメールアドレスが登録されてません。');
        }
    
        if ($admin) {
            // 新しいパスワードをハッシュ化して保存
            $admin->password = bcrypt($request['new_password']);
            $admin->save();
        }
    
        if ($employee) {
            // 新しいパスワードをハッシュ化して保存
            $employee->password = bcrypt($request['new_password']);
            $employee->save();
        }
    
        return redirect()->back()->with('success', 'パスワードが正常にリセットされました。');
    }    
}
