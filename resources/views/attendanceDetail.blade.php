<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ出勤簿</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 10px;
        }

        .staff-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .staff-info p {
            margin: 5px 0;
            font-size: 1.2em;
            color: #333;
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
            background-color: #007bff;
            color: #fff;
        }

        .summary {
            text-align: right;
            margin-top: 20px;
            font-size: 1.2em;
        }

        .summary span {
            font-weight: bold;
            color: #007bff;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            text-decoration: none;
            color: #007bff;
            font-size: 1em;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
        .error-icon {
            color: #dc3545;
            font-weight: bold;
            margin-left: 5px;
            font-size: 1.2em;
            cursor: pointer;
            position: relative;
        }

        .error-icon::after {
            content: attr(title);
            position: absolute;
            bottom: 120%; /* 上に表示 */
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            white-space: nowrap;
            display: none;
            color: red;
        }

        .error-icon:hover::after {
            display: block;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>スタッフ出勤簿</h1>

        <div class="staff-info">
            <p>名前: {{ $employee->name }}</p>
            <p>メール: {{ $employee->email }}</p>
            <p>時給: ¥{{ number_format($employee->hourly_wage) }}</p>
            <p>交通費: ¥{{ number_format($employee->transportation_fee) }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>労働時間</th>
                    <th>給与</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceData as $data)
                <tr @if (!empty($data['error']))
                    class="error-row"
                    @endif>
                    <td>@if (!empty($data['error']))
                            <span class="error-tooltip">
                                打刻が不正です
                            </span>
                        @endif
                        <a href="{{ route('attendanceDetails', ['date' => $data['date'], 'employeeId' => $employee->id, 'companyId' => $employee->company_id]) }}">
                            {{ $data['date'] }}
                        </a>
                    </td>
                    @php
                        $totalMinutes = $data['work_hours'] * 60; // 時間を分に変換
                        $hours = floor($totalMinutes / 60); // 時間部分
                        $minutes = $totalMinutes % 60; // 分部分
                    @endphp
                    <td>{{ sprintf('%02d時間%02d分', $hours, $minutes) }}</td>
                    <td>¥{{ number_format($data['salary']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <p>総労働時間: <span>{{ $totalWorkHours }} 時間</span></p>
            <p>総給与: <span>¥{{ number_format($totalSalary) }}</span></p>
        </div>

        <div class="back-link">
            <a href="{{ route('attendanceList', ['companyId' => $employee->company_id]) }}">出勤簿一覧に戻る</a>
        </div>
    </div>
</body>
</html>
