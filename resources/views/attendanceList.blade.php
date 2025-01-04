<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ出勤簿一覧</title>
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
            background: #007bff;
            color: white;
        }

        .view-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .back-btn:hover {
            opacity: 0.9;
        }

        .error-icon {
            color: #dc3545;
            font-weight: bold;
            margin-left: 5px;
            font-size: 1.2em;
            position: relative;
            cursor: pointer;
        }

        .error-icon::after {
            content: "打刻エラーあり";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 120%; /* ビックリマークの上に表示 */
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            white-space: nowrap;
            display: none; /* デフォルトは非表示 */
            color: red;
        }

        .error-icon:hover::after {
            display: block; /* ホバー時に表示 */
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
    </style>
</head>
<body>
    <div class="container">
        <h1>スタッフ出勤簿一覧</h1>
        <a href="{{ route('attendance.export', ['companyId' => Auth::user()->id, 'year' => $currentYear, 'month' => $currentMonth]) }}" class="excel-export-button">
            エクセルを出力
        </a>

        <div class="month-navigation">
            <a href="{{ route('attendanceList', ['companyId' => Auth::User()->id, 'year' => $currentYear, 'month' => $currentMonth - 1]) }}" class="nav-button">前の月</a>
            <span>{{ $currentYear }}年 {{ $currentMonth }}月</span>
            <a href="{{ route('attendanceList', ['companyId' => Auth::User()->id, 'year' => $currentYear, 'month' => $currentMonth + 1]) }}" class="nav-button">次の月</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤日数</th>
                    <th>総労働時間</th>
                    <th>総給与</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                <tr>
                    <td>
                        @if ($employee->hasErrors)
                            <span class="error-icon">&#33;</span>
                        @endif
                        {{ $employee['name'] }}
                    </td>
                    <td>{{ $employee['attendanceDays'] }}</td>
                    <td>{{ $employee['totalWorkHours'] }} 時間</td>
                    <td>¥{{ number_format($employee['totalSalary']) }}</td>
                    <td>
                        <a href="{{ route('attendanceDetail', ['employeeId' => $employee['id'], 'year' => $currentYear, 'month' => $currentMonth]) }}" class="view-link">詳細</a>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
        <a href="{{ route('staff') }}" class="back-btn">戻る</a>
    </div>
</body>
</html>
