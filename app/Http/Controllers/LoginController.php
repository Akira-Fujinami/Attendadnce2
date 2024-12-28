<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|email',
        //     'password' => 'required|min:6',
        // ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->route('staff')->with('success', 'ログインに成功しました');
        }

        if (Auth::guard('employees')->attempt($request->only('email', 'password'))) {
            return redirect()->route('adit')->with('success', '従業員としてログインしました');
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
}
