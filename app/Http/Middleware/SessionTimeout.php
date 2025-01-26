<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle($request, Closure $next)
    {
        // ログインしている場合のみ処理
        if (Auth::check()) {
            $lastActivity = session('lastActivityTime'); // セッションから最終操作時間を取得
            $currentTime = now()->timestamp; // 現在のタイムスタンプを取得

            // 最終操作時間があり、5分以上経過している場合
            if ($lastActivity && ($currentTime - $lastActivity > 5 * 60)) {
                Auth::logout(); // ログアウト処理
                return redirect()->route('login')->with('error', '5分間操作がなかったためログアウトしました。');
            }

            // 現在のタイムスタンプをセッションに保存
            session(['lastActivityTime' => $currentTime]);
        }

        return $next($request);
    }
}
