<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        // バリデーションルールの適用（カスタムルールを含む）
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email', // usersテーブルで重複チェック
                'unique:employees,email', // employeesテーブルで重複チェック
            ],
            'password' => 'required|string|min:3|confirmed',
        ],[
            'name.required' => '名前は必須です。',
            'name.string' => '名前は文字列で入力してください。',
            'name.max' => '名前は255文字以内で入力してください。',
            
            'email.required' => 'メールアドレスは必須です。',
            'email.string' => 'メールアドレスは文字列で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは255文字以内で入力してください。',
            'email.unique' => 'このメールアドレスはすでに登録されています。',
            
            'password.required' => 'パスワードは必須です。',
            'password.string' => 'パスワードは文字列で入力してください。',
            'password.min' => 'パスワードは3文字以上で入力してください。',
            'password.confirmed' => 'パスワードが確認用と一致しません。',
        ]);

        // データベースに保存

        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 成功メッセージと共にリダイレクト
        return redirect()->route('complete');
    }
}
