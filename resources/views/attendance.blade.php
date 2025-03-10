<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出勤簿</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .summary {
            text-align: right;
            font-size: 1.2em;
        }
        .summary p {
            margin: 0;
        }
        .return-button {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .return-button button {
            font-size: 1em;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .return-button button:hover {
            background-color: #0056b3;
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

        .month-navigation {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            font-size: 1.2em;
            color: #333;
        }

        .month-navigation .nav-button {
            padding: 10px 15px;
            margin: 0 10px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .month-navigation .nav-button:hover {
            background-color: #0056b3;
        }

        .month-navigation .current-month {
            font-size: 1.5em;
            font-weight: bold;
            color: #007bff;
        }
        /* エラー行の通常状態 */
        .error-row {
            background-color: #ff69b4; /* ピンク背景 */
            position: relative;
            transition: background-color 0.3s ease;
        }

        /* ホバー時に背景色を強調 */
        .error-row:hover {
            background-color: #ff3366; /* より濃いピンク */
            box-shadow: 0px 0px 15px rgba(255, 51, 102, 0.75);
        }

        /* 承認待ち（pending）の行の背景色 */
        .pending-row {
            background-color: #fff3cd; /* 黄色 */
            position: relative;
            transition: background-color 0.3s ease;
        }

        /* ホバー時に背景色を強調 */
        .pending-row:hover {
            background-color: #ffeeba; /* より濃い黄色 */
            box-shadow: 0px 0px 15px rgba(255, 193, 7, 0.75);
        }

        /* ツールチップのデザイン */
        .error-tooltip, .pending-tooltip{
            position: absolute;
            visibility: hidden;
            width: 220px;
            background-color: rgba(0, 0, 0, 0.85);
            color: #fff;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            bottom: 110%; /* 上に表示 */
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
            font-size: 0.9em;
            z-index: 10;
        }

        /* 行にカーソルを合わせた際にツールチップを表示 */
        .error-row:hover .error-tooltip,
        .pending-row:hover .pending-tooltip{
            visibility: visible;
            opacity: 1;
            transform: translateX(-50%) translateY(-5px);
        }

        /* エラーアイコンの点滅 */
        @keyframes blink {
            50% { opacity: 0; }
        }
        .error-icon {
            font-size: 1.5em;
            font-weight: bold;
            color: red;
            animation: blink 1s infinite;
            cursor: help;
        }
        .logout-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.65;
        }

        @media screen and (max-width: 768px) {
            .month-navigation {
                flex-direction: column;
            }

            .nav-button {
                width: 100%;
                margin-bottom: 5px;
            }
            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .button {
                width: 100%; /* スマホでは全幅 */
                max-width: 250px;
            }

            h1 {
                font-size: 1.5em; /* 文字を小さく */
            }
        }
        @media screen and (max-width: 768px) {
            table {
                display: block; /* テーブル全体をブロック表示 */
                overflow-x: auto; /* 横スクロールを有効に */
                white-space: nowrap; /* 折り返しを防ぐ */
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutForm = document.getElementById("logout-form");
            const logoutButton = document.getElementById("logout-btn");

            logoutForm.addEventListener("submit", function(event) {
                logoutButton.disabled = true; // ボタンを無効化
                logoutButton.textContent = "処理中..."; // ログイン中のメッセージに変更
            });
        });
    </script>
</head>
<body>
    <!-- 打刻画面へのリンク -->
    <div class="return-button">
        <button onclick="location.href='{{ route('adit') }}'">打刻画面に戻る</button>
    </div>
    @if($event)
    <div class="return-button">
        <button onclick="location.href='{{ route('adit_qr') }}'">打刻画面に戻る</button>
    </div>
    @endif
        <!-- ログアウトボタン -->
    <div class="logout">
        <form action="{{ route('logout') }}" method="POST" id="logout-form">
            @csrf
            <button type="submit" class="button" id="logout-btn">ログアウト</button>
        </form>
    </div>
    <h1>出勤簿</h1>
    <div class="month-navigation">
        <a href="{{ route('attendance', ['employee_id' => $employeeId, 'company_id' => Auth::user()->company_id, 'year' => $currentYear, 'month' => $currentMonth - 1]) }}" class="nav-button">◀ 前の月</a>
        <span class="current-month">{{ $currentYear }}年 {{ $currentMonth }}月</span>
        <a href="{{ route('attendance', ['employee_id' => $employeeId, 'company_id' => Auth::user()->company_id, 'year' => $currentYear, 'month' => $currentMonth + 1]) }}" class="nav-button">次の月 ▶</a>
    </div>

    <div class="info">
        <p><strong>スタッフ名:</strong> {{ $name }}</p>
        <p><strong>総労働時間:</strong> {{ $totalWorkHours }} </p>
        <p><strong>総休憩時間:</strong> {{ $totalBreakHours}} </p>
    </div>
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>労働時間</th>
                <th>休憩時間</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
                    @php
                        $recordsForDate = $attendanceRecords[$date] ?? null;

                        // 各日付の打刻情報を初期化
                        $workStart = $recordsForDate ? $recordsForDate['records']->firstWhere('adit_item', 'work_start') : null;
                        $workEnd = $recordsForDate ? $recordsForDate['records']->firstWhere('adit_item', 'work_end') : null;
                        $breakStart = $recordsForDate ? $recordsForDate['records']->firstWhere('adit_item', 'break_start') : null;
                        $breakEnd = $recordsForDate ? $recordsForDate['records']->firstWhere('adit_item', 'break_end') : null;

                        // 労働時間と休憩時間を計算
                        $workHours = 0;
                        $breakHours = 0;

                        if ($workStart && $workEnd) {
                            $workStartTime = \Carbon\Carbon::parse($workStart['minutes']);
                            $workEndTime = \Carbon\Carbon::parse($workEnd['minutes']);
                            $workMinutes = $workStartTime->diffInMinutes($workEndTime);

                            $workHours = floor($workMinutes / 60) + ($workMinutes % 60) / 100; // 時間に変換
                        }

                        if ($breakStart && $breakEnd) {
                            $breakStartTime = \Carbon\Carbon::parse($breakStart['minutes']);
                            $breakEndTime = \Carbon\Carbon::parse($breakEnd['minutes']);
                            $breakMinutes = $breakStartTime->diffInMinutes($breakEndTime);

                            $breakHours = floor($breakMinutes / 60) + ($breakMinutes % 60) / 100; // 時間に変換
                        }
                        $weekdays = ['Sun' => '日', 'Mon' => '月', 'Tue' => '火', 'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土'];
                        $parsedDate = \Carbon\Carbon::parse($date);
                        $formattedDate = $parsedDate->format('n/j') . ' (' . $weekdays[$parsedDate->format('D')] . ')';
                    @endphp
                    <tr @if ($recordsForDate && $recordsForDate['error'])
                            class="error-row"
                        @elseif ($recordsForDate && $recordsForDate['has_pending'])
                            class="pending-row"
                        @endif>
                    <td>
                        @if ($recordsForDate && $recordsForDate['error'])
                            <span class="error-tooltip">
                                打刻が不正です
                            </span>
                        @elseif ($recordsForDate && $recordsForDate['has_pending'])
                            <span class="pending-tooltip">
                                未承認の打刻があります<br>
                                承認されると時刻が反映されます
                            </span>
                        @endif
                        <a href="{{ route('editAttendance', ['date' => $date, 'employeeId' => $employeeId]) }}" class="date-link">{{ $formattedDate }}</a>
                    </td>
                    <td>
                        @if ($recordsForDate)
                            @foreach ($recordsForDate['records'] as $record)
                                @if ($record['adit_item'] === 'work_start' && !is_null($record['minutes']))
                                    {{ \Carbon\Carbon::parse($record['minutes'])->format('H:i') }}
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>
                        @if ($recordsForDate)
                            @foreach ($recordsForDate['records'] as $record)
                                @if ($record['adit_item'] === 'work_end' && !is_null($record['minutes']))
                                    {{ \Carbon\Carbon::parse($record['minutes'])->format('H:i') }}
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>
                        @if (!is_null($recordsForDate) && isset($recordsForDate['work']))
                            @php
                                $totalMinutes = $recordsForDate['work'] * 60; // 時間を分に変換
                                $hours = floor($totalMinutes / 60); // 時間部分
                                $minutes = $totalMinutes % 60; // 分部分
                            @endphp
                            {{ sprintf('%02d時間%02d分', $hours, $minutes) }}
                        @else
                            00時間00分
                        @endif
                    </td>
                    <td>
                    @if (!is_null($recordsForDate) && isset($recordsForDate['break']))
                        @php
                            $totalMinutes = $recordsForDate['break'] * 60; // 時間を分に変換
                            $hours = floor($totalMinutes / 60); // 時間部分
                            $minutes = $totalMinutes % 60; // 分部分
                        @endphp
                        {{ sprintf('%02d時間%02d分', $hours, $minutes) }}
                    @else
                        00時間00分
                    @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
