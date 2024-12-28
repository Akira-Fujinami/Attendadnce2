<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出勤管理システム</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 50px;
        }

        h1 {
            color: #333;
        }

        .time-display {
            font-size: 2em;
            color: #333;
            margin-top: 20px;
        }

        .button-group {
            margin-top: 30px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1.2em;
            margin: 10px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .button-green {
            background-color: #28a745;
        }

        .button-yellow {
            background-color: #ffc107;
        }

        .button-blue {
            background-color: #17a2b8;
        }

        .button-red {
            background-color: #dc3545;
        }

        .button:hover {
            opacity: 0.9;
        }

        .navigation {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .navigation .button {
            font-size: 1em;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
        }

        .navigation .button:hover {
            opacity: 0.9;
        }

        .logout {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .logout .button {
            font-size: 1em;
            padding: 10px 15px;
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
        }

        .logout .button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- 出勤簿へ遷移ボタン -->
    <div class="navigation">
        <a href="{{ route('attendance') }}" class="button">出勤簿へ遷移</a>
    </div>

    <!-- ログアウトボタン -->
    <div class="logout">
        <a href="{{ route('login') }}" class="button">ログアウト</a>
    </div>

    <div class="container">
        <h1>現在の時間</h1>
        <div class="time-display" id="current-time">
            <!-- 時間がここに表示されます -->
        </div>

        <div class="button-group">
            <button class="button button-green">出勤</button>
            <button class="button button-yellow">休憩開始</button>
            <button class="button button-blue">休憩終了</button>
            <button class="button button-red">退勤</button>
        </div>
    </div>

    <script>
        // 時間を動的に更新する関数
        function updateTime() {
            const now = new Date(); // 現在の日時を取得
            const hours = String(now.getHours()).padStart(2, '0'); // 時間を2桁に整形
            const minutes = String(now.getMinutes()).padStart(2, '0'); // 分を2桁に整形
            const seconds = String(now.getSeconds()).padStart(2, '0'); // 秒を2桁に整形
            const formattedTime = `${hours}:${minutes}:${seconds}`; // "HH:mm:ss"形式で表示

            document.getElementById('current-time').textContent = formattedTime; // HTMLに表示
        }

        // 初期表示
        updateTime();

        // 毎秒時間を更新
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
