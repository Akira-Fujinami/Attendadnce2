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
                <th>休憩開始</th>
                <th>休憩終了</th>
                <th>退勤</th>
                <th>労働時間</th>
            </tr>
        </thead>
        <tbody>
            <!-- サンプルデータを表示 -->
            <tr>
                <td><a href="{{ route('change') }}" class="button">2024-12-01</a></td>
                <td>09:00</td>
                <td>12:00</td>
                <td>13:00</td>
                <td>18:00</td>
                <td>8.00 時間</td>
            </tr>
            <tr>
                <td>2024-12-02</td>
                <td>09:30</td>
                <td>12:30</td>
                <td>13:30</td>
                <td>18:30</td>
                <td>8.00 時間</td>
            </tr>
            <tr>
                <td>2024-12-03</td>
                <td>10:00</td>
                <td>13:00</td>
                <td>14:00</td>
                <td>19:00</td>
                <td>8.00 時間</td>
            </tr>
            <tr>
                <td>2024-12-04</td>
                <td>09:15</td>
                <td>12:15</td>
                <td>13:15</td>
                <td>18:15</td>
                <td>8.00 時間</td>
            </tr>
            <tr>
                <td>2024-12-05</td>
                <td>09:00</td>
                <td>12:00</td>
                <td>13:00</td>
                <td>17:00</td>
                <td>7.00 時間</td>
            </tr>
        </tbody>
    </table>
    <div class="summary">
        <p>働いた日数: <strong>5</strong> 日</p>
        <p>総労働時間: <strong>39.00</strong> 時間</p>
    </div>
</body>
</html>
