<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ一覧</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }

        .add-staff-btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .add-staff-btn:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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

        .status {
            padding: 5px 10px;
            border-radius: 5px;
            color: #fff;
        }

        .status-working {
            background-color: #28a745;
        }

        .status-break {
            background-color: #ffc107;
        }

        .status-retired {
            background-color: #dc3545;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('staffCreate') }}" class="add-staff-btn">新しいスタッフを追加</a>
        <a href="{{ route('attendance3') }}" class="add-staff-btn">出勤簿</a>
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="add-staff-btn">ログアウト</button>
        </form>
        <h1>スタッフ一覧</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>勤務状況</th>
                    <th>アクション</th>
                    <th>出勤簿</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>001</td>
                    <td>山田 太郎</td>
                    <td><span class="status status-working">勤務中</span></td>
                    <td><a href="{{ route('staffDetail') }}" class="button">詳細を見る</a></td>
                    <td><a href="{{ route('attendance2') }}" class="button">出勤簿を見る</a></td>
                </tr>
                <tr>
                    <td>002</td>
                    <td>佐藤 花子</td>
                    <td><span class="status status-break">休憩中</span></td>
                    <td><a href="#">詳細を見る</a></td>
                    <td><a href="#">出勤簿を見る</a></td>
                </tr>
                <tr>
                    <td>003</td>
                    <td>鈴木 次郎</td>
                    <td><span class="status status-retired">退勤</span></td>
                    <td><a href="#">詳細を見る</a></td>
                    <td><a href="#">出勤簿を見る</a></td>
                </tr>
            </tbody>
        </table>
        <footer>© 2024 勤怠管理システム. All Rights Reserved.</footer>
    </div>
</body>
</html>
