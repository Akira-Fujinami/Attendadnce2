<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * グローバルミドルウェアスタック
     *
     * すべてのリクエストで実行されるミドルウェア
     *
     * @var array
     */
    protected $middleware = [
        // HTTPプロキシを信頼する
        \App\Http\Middleware\TrustProxies::class,
        // CORS (Cross-Origin Resource Sharing) を処理する
        \Illuminate\Http\Middleware\HandleCors::class,
        // メンテナンスモードを確認
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        // POSTリクエストのサイズを確認
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // 文字列をトリムする
        \App\Http\Middleware\TrimStrings::class,
        // 空文字列をnullに変換
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * ミドルウェアグループの定義
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // 認証時のエラーを共有
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // CSRF保護
            \App\Http\Middleware\VerifyCsrfToken::class,
            // ルートのパラメータを置き換える
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // APIのリクエストレートを制限
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * アプリケーションのルートミドルウェア
     *
     * 個別のルートで指定可能
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'session.timeout' => \App\Http\Middleware\SessionTimeout::class,
    ];
}
