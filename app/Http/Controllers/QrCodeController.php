<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class QrCodeController extends Controller
{
    public function show($eventId)
    {
        $event = Event::findOrFail($eventId);
        $fromTimestamp = Carbon::parse($event->fromDate)->timestamp;
        $toTimestamp = Carbon::parse($event->toDate)->timestamp;

        // QRコードに埋め込むURL（ログインページにイベントIDを付与）
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . 
        urlencode(route('qr.login', [
            'event_id' => $event->id,
            'from' => $fromTimestamp,
            'to' => $toTimestamp
        ]));

        return view('qr', compact('event', 'qrCodeUrl'));
    }

    public function download($eventId)
    {
        $event = Event::findOrFail($eventId);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode(route('qr.login', ['event_id' => $event->id]));

        // QRコード画像を取得
        $response = Http::get($qrCodeUrl);

        if ($response->failed()) {
            return back()->withErrors('QRコードの取得に失敗しました。');
        }

        // 画像をダウンロード用にレスポンス
        return Response::make($response->body(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="event_'.$event->id.'_qr.png"',
        ]);
    }
}
