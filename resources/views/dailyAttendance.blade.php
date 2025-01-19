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
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $selectedDate }}の日次打刻データ</h1>
        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>打刻記録</th>
                    <th>労働時間 (分)</th>
                    <th>給与 (円)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceData as $data)
                <tr>
                    <td>{{ $data['employee']->name }}</td>
                    @php
                        $totalMinutes = $data['totalDailyBreakHours'] * 60; // 時間を分に変換
                        $hours = floor($totalMinutes / 60); // 時間部分
                        $minutes = $totalMinutes % 60; // 分部分
                    @endphp
                    <td>{{ sprintf('%02d時間%02d分', $hours, $minutes) }}</td>
                    @php
                        $totalMinutes = $data['totalDailyWorkHours'] * 60; // 時間を分に変換
                        $hours = floor($totalMinutes / 60); // 時間部分
                        $minutes = $totalMinutes % 60; // 分部分
                    @endphp
                    <td>{{ sprintf('%02d時間%02d分', $hours, $minutes) }}</td>
                    <td>¥{{ number_format($data['totalDailySalary']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <a href="{{ route('showCalendar') }}">カレンダーに戻る</a>
    </div>
</body>
</html>
