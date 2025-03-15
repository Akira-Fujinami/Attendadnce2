<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Str;

class QrLoginController extends Controller
{
    public function qrScanned(Request $request)
    {
        // ✅ QRコードをスキャンしたことをセッションに記録
        session(['qr_scanned' => true]);

        return redirect()->route('qr.login', [
            'event_id' => $request->input('event_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to')
        ]);
    }
    public function directAccessError()
    {
        return abort(403, '不正なアクセスです。QRコードをスキャンしてください。');
    }


    // QRコードログインページを表示
    public function showLoginForm(Request $request)
    {
        if (!$request->session()->has('qr_scanned')) {
            return redirect()->route('login.error');
        }
        $eventId = $request->input('event_id');
        $event = Event::find($eventId);

        if (!$event) {
            return redirect()->route('events.index')->withErrors('イベントが見つかりません。');
        }

        $fromTimestamp = $request->input('from');
        $toTimestamp = $request->input('to');
    
        // 現在の時間（UNIXタイムスタンプ）
        $currentTimestamp = now()->timestamp;
    
        // 有効期限のチェック
        if ($currentTimestamp < $fromTimestamp || $currentTimestamp > $toTimestamp) {
            return redirect()->route('qr.expired'); // 有効期限切れページにリダイレクト
        }
    
        session(['qr_scanned' => true]);

        return view('qr-login', compact('eventId', 'event'));
    }

    // QRコードログイン処理
    public function login(Request $request)
    {
        if (!session('qr_scanned')) {
            return abort(403, '不正なアクセスです。QRコードをスキャンしてください。');
        }
        session()->forget('qr_scanned');
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'event_id' => 'required|exists:events,id'
        ]);

        $credentials = $request->only('email', 'password');
        $eventId = $request->input('event_id');

        if (Auth::guard('employees')->attempt($credentials)) {
            $employee = Auth::guard('employees')->user();
            session([
                'saved_email' => $employee->email,
                'saved_password' => $request->input('password'),
                'event' => Event::find($eventId)->name,
                'evId' => Event::find($eventId)->id,
            ]);
            Auth::guard('employees')->login($employee);
            session()->forget('qr_scanned');
            return redirect()->route('adit_qr', ['event' => $eventId])
                ->withCookie(cookie('email', $request->email, 60 * 24 * 30))
                ->with('success', '従業員としてログインしました');
        }

        return back()->withErrors(['email' => 'ログイン情報が正しくありません']);
    }

    public function logout(Request $request)
    {
        $eventId = $request->input('event_id');
    
        // ログアウト前にメールとパスワードを保存
        $savedEmail = session('saved_email');
        $savedPassword = session('saved_password');
    
        Auth::guard('employees')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        // セッションを再作成し、メールとパスワードを復元
        session([
            'saved_email' => $savedEmail,
            'saved_password' => $savedPassword,
        ]);
    
        return redirect()->route('qr.login', ['event_id' => $eventId]); 
    }
}    
