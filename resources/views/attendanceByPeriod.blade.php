<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>期間別出勤簿</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        .filter-form {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-form input {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .filter-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-form button:hover {
            background-color: #0056b3;
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
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        @media screen and (max-width: 768px) {
            table {
                display: block; /* テーブル全体をブロック表示 */
                overflow-x: auto; /* 横スクロールを有効に */
                white-space: nowrap; /* 折り返しを防ぐ */
            }
            .date-container {
                display: flex;
                flex-direction: column;  /* 縦方向に並べる */
                align-items: center;      /* 中央揃え */
                gap: 10px;               /* 間隔を10pxあける */
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>期間別出勤簿</h1>

        <!-- 期間選択フォーム -->
        <form action="{{ route('attendance.byPeriod') }}" method="GET" class="filter-form">
            <div class="date-container">
                <label>開始日:</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" required>
                <label>終了日:</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" required>
                <button type="submit">検索</button>
            </div>
        </form>

        <!-- 出勤データ表示 -->
        @if(isset($attendances))
        <a href="{{ route('attendance.period.export', ['companyId' => Auth::user()->id, 'startDate' => $start_date, 'endDate' => $end_date]) }}" class="excel-export-button">
            エクセルを出力
        </a>
        <table>
            <thead>
                <tr>
                    <th>従業員名</th>
                    <th>出勤日数</th>
                    <th>労働時間</th>
                    <th>休憩時間</th>
                    <th>給与</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->employee_name }}</td>
                    <td>{{ $attendance->attendance_days }} 日</td>
                    <td>
                        @php
                            $totalMinutes = $attendance['work_minutes'] * 60; // 時間を分に変換
                            $hours = floor($totalMinutes / 60); // 時間部分
                            $minutes = $totalMinutes % 60; // 分部分
                        @endphp
                        {{ sprintf('%02d時間%02d分', $hours, $minutes) }}
                    </td>
                    <td>
                        @php
                            $totalMinutes = $attendance['break_minutes'] * 60; // 時間を分に変換
                            $hours = floor($totalMinutes / 60); // 時間部分
                            $minutes = $totalMinutes % 60; // 分部分
                        @endphp
                        {{ sprintf('%02d時間%02d分', $hours, $minutes) }}
                    </td>
                    <td>¥{{ number_format($attendance->salary) }}</td>
                    <td>
                    <a href="{{ route('attendanceDetail', [
                        'employeeId' => $attendance['employeeId'],
                        'start' => $start_date,
                        'end' => $end_date,
                        'period' => 1
                    ]) }}" class="btn">
                        詳細
                    </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        <a href="{{ route('top') }}" class="back-button">トップに戻る</a>
    </div>
</body>
</html>
