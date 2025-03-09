<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRコードでログイン</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        h1 {
            color: #007bff;
        }

        .qr-code img {
            max-width: 250px;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $event->name }}</h1>
        <p>このQRコードをスキャンしてログインしてください。</p>
        <div class="qr-code">
            <img src="{{ $qrCodeUrl }}" alt="QRコード">
        </div>
        <a href="{{ route('events.index') }}" class="btn">⬅ イベント一覧に戻る</a>
        <a href="{{ route('qr.download', ['event' => $event->id]) }}" class="btn">📥 QRコードをダウンロード</a>
    </div>
</body>
</html>
