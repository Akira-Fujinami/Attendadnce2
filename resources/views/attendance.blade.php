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
    </style>
</head>
<body>
    <h1>出勤簿</h1>
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
            <tr>
                <td>{{ $date }}</td>
                <td>
                    @if(isset($attendanceRecords[$date]) && $attendanceRecords[$date]->adit_item === 'work_start')
                        {{ \Carbon\Carbon::parse($attendanceRecords[$date]->minutes)->format('H:i') }}
                    @endif
                </td>
                <td>
                    @if(isset($attendanceRecords[$date]) && $attendanceRecords[$date]->adit_item === 'work_end')
                        {{ \Carbon\Carbon::parse($attendanceRecords[$date]->minutes)->format('H:i') }}
                    @endif
                </td>
                <td>{{ $attendanceRecords[$date]->work_hours ?? '0.00' }} 時間</td>
                <td>{{ $attendanceRecords[$date]->break_hours ?? '0.00' }} 時間</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
