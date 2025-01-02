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

        .logout form {
            display: inline;
        }

        .logout .button {
            font-size: 1em;
            padding: 10px 15px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout .button:hover {
            background-color: #c82333;
        }

        .button[disabled] {
            background-color: #6c757d; /* グレー色 */
            color: #fff; /* テキスト色 */
            cursor: not-allowed; /* ポインターを無効化表示 */
            opacity: 0.65; /* 半透明 */
        }
        .error-link {
            color: #dc3545; /* 赤色 */
            text-decoration: none;
            font-weight: bold;
        }

        .error-link:hover {
            text-decoration: underline;
            opacity: 0.8; /* マウスホバー時に少し透明感を追加 */
        }
    </style>
</head>
<body>
    <!-- 出勤簿へ遷移ボタン -->
    <div class="navigation">
        <a href="{{ route('attendance', ['company_id' => Auth::user()->company_id, 'employee_id' => Auth::user()->id]) }}" class="button">出勤簿へ遷移</a>
    </div>

    <!-- ログアウトボタン -->
    <div class="logout">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="button">ログアウト</button>
        </form>
    </div>

    <div class="container">
            <div style="margin-top: 20px; font-size: 1.5em;
            @if($data['latestAdit'] == 'work_start') color: #28a745; 
               @elseif($data['latestAdit'] == 'break_start') color: #ffc107
               @elseif($data['latestAdit'] == 'break_end') color: #28a745
               @elseif($data['latestAdit'] == 'work_end') color: #dc3545 
               @endif">
                {{$data['status']}}
            </div>
        <h1>現在の時間</h1>
        <div class="time-display" id="current-time">
            <!-- 時間がここに表示されます -->
        </div>

        <div class="button-group">
    <form action="{{ route('adit') }}" method="POST">
        @csrf
        <button type="submit" name="adit_item" value="work_start" class="button button-green" @if($data['aditExists'] or $data['latestAdit'] == 'work_end') disabled @endif>出勤</button>
        <button type="submit" name="adit_item" value="break_start" class="button button-yellow" @if(!$data['aditExists'] or $data['latestAdit'] == 'work_end' or $data['latestAdit'] == 'break_start') disabled @endif>休憩開始</button>
        <button type="submit" name="adit_item" value="break_end" class="button button-blue" @if(!$data['aditExists'] or $data['latestAdit'] == 'work_end' or $data['latestAdit'] == 'work_start' or $data['latestAdit'] == 'break_end') disabled @endif>休憩終了</button>
        <button type="submit" name="adit_item" value="work_end" class="button button-red" @if(!$data['aditExists'] or $data['latestAdit'] == 'work_end' or $data['latestAdit'] == 'break_start') disabled @endif>退勤</button>
        <input type="hidden" name="employee_id" value="{{ Auth::user()->id }}">
        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
        <input type="hidden" name="wage" value="{{ Auth::user()->hourly_wage }}">
        <input type="hidden" name="transportation" value="{{ Auth::user()->transportation_fee }}">
    </form>
    @foreach($data['errors'] as $error)
        <li>
            <a href="{{ route('editAttendance', ['date' => $error['date'], 'employeeId' => Auth::User()->id]) }}" class="error-link">
                {{ $error['date'] }}: {{ is_array($error['error']) ? implode(', ', $error['error']) : $error['error'] }}
            </a>
        </li>
    @endforeach

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
