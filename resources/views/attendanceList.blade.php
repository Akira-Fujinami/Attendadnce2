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
    </style>
</head>
<body>
    <div class="container">
        <h1>スタッフ出勤簿一覧</h1>
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
                    <td>{{ $employee['name'] }}</td>
                    <td>{{ $employee['attendanceDays'] }}</td>
                    <td>{{ $employee['totalWorkHours'] }} 時間</td>
                    <td>¥{{ number_format($employee['totalSalary']) }}</td>
                    <td><a href="{{ route('attendanceDetail', ['employeeId' => $employee['id']]) }}" class="view-link">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <a href="{{ route('staff') }}" class="back-btn">戻る</a>
    </div>
</body>
</html>
