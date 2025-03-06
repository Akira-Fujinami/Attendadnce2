<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $selectedDate }}の日次打刻データ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f4f7f6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1 {
            color: #007bff;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        .summary {
            text-align: right;
            margin-top: 20px;
            font-size: 1.2em;
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

        /* ツールチップのデザイン */
        .error-tooltip {
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
        .error-row:hover .error-tooltip {
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

        .excel-export-button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #28a745; /* グリーン系の背景色 */
            color: white; /* テキストは白 */
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em; /* 少し大きめのフォント */
            font-weight: bold;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* ボタンの影 */
        }

        .excel-export-button:hover {
            background-color: #218838; /* ホバー時に少し濃いグリーン */
            transform: translateY(-2px); /* 少し浮き上がる */
        }

        .excel-export-button:active {
            background-color: #1e7e34; /* クリック時の色 */
            transform: translateY(0); /* 元に戻る */
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1); /* 影を小さくする */
        }
        @media screen and (max-width: 768px) {
            table {
                display: block; /* テーブル全体をブロック表示 */
                overflow-x: auto; /* 横スクロールを有効に */
                white-space: nowrap; /* 折り返しを防ぐ */
            }
            h1 {
                font-size: 1.3em; /* フォントサイズを少し小さくする */
                padding: 0 10px; /* 余白を追加 */
                max-width: 100%; /* 親要素に収める */
                text-align: center; /* 中央揃え */
                white-space: normal; /* 必要に応じて改行 */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @php
            $weekdays = ['Sun' => '日', 'Mon' => '月', 'Tue' => '火', 'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土'];
            $parsedDate = \Carbon\Carbon::parse($selectedDate);
            $formattedDate = $parsedDate->format('Y/n/j') . ' (' . $weekdays[$parsedDate->format('D')] . ')';
        @endphp
        <h1>{{ $formattedDate }}</h1>
        <a href="{{ route('dailyAttendance.export', ['companyId' => Auth::user()->id, 'date' => $selectedDate]) }}" class="excel-export-button">
            エクセルを出力
        </a>
        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>労働時間</th>
                    <th>休憩時間</th>
                    <th>給与 (円)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceData as $data)
                <tr @if ($data['error'] == 1))
                    class="error-row"
                    @endif>
                    <td>
                        @if ($data['error'] == 1)
                            <span class="error-tooltip">
                                打刻が不正です
                            </span>
                        @endif
                        {{ $data['employee']->name }}
                    </td>
                    @php
                        $totalMinutes = $data['totalDailyWorkHours'] * 60; // 時間を分に変換
                        $hours = floor($totalMinutes / 60); // 時間部分
                        $minutes = $totalMinutes % 60; // 分部分
                    @endphp
                    <td>{{ sprintf('%02d時間%02d分', $hours, $minutes) }}</td>
                    @php
                        $totalMinutes = $data['totalDailyBreakHours'] * 60; // 時間を分に変換
                        $hours = floor($totalMinutes / 60); // 時間部分
                        $minutes = $totalMinutes % 60; // 分部分
                    @endphp
                    <td>{{ sprintf('%02d時間%02d分', $hours, $minutes) }}</td>
                    <td>¥{{ number_format($data['totalDailySalary']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="summary">
            <p>総給与: <span>¥{{ number_format($totalSalary) }}</span></p>
        </div>
        <a href="{{ route('showCalendar') }}">カレンダーに戻る</a>
    </div>
</body>
</html>
