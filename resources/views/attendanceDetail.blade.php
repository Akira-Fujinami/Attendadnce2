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
    </style>
</head>
<body>
    <div class="container">
        <h1>スタッフ出勤簿</h1>

        <div class="staff-info">
            <p>名前: {{ $employee->name }}</p>
            <p>メール: {{ $employee->email }}</p>
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
                <tr>
                    <td>{{ $data['date'] }}</td>
                    <td>{{ $data['work_hours'] }} 時間</td>
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
            <a href="{{ route('staff') }}">スタッフ一覧に戻る</a>
        </div>
    </div>
</body>
</html>
