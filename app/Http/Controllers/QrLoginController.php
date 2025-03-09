<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Event;
use Carbon\Carbon;

class QrLoginController extends Controller
{
    // QRコードログインページを表示
    public function showLoginForm(Request $request)
    {
        $eventId = $request->input('event_id');
        $event = Event::find($eventId);

        if (!$event) {
            return redirect()->route('events.index')->withErrors('イベントが見つかりません。');
        }

        return view('qr-login', compact('eventId', 'event'));
    }

    // QRコードログイン処理
    public function login(Request $request)
    {
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
            ]);
            Auth::guard('employees')->login($employee);
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
