<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ詳細</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 1.2em;
        }

        .detail-item span {
            color: #555;
        }

        .password-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .password-section span {
            font-size: 1.2em;
            color: #555;
            margin-right: 10px;
        }

        .password-section button {
            padding: 5px 10px;
            font-size: 0.9em;
            cursor: pointer;
        }

        .status {
            padding: 10px 20px;
            color: #fff;
            border-radius: 5px;
            font-size: 1em;
        }

        .status-active {
            background-color: #28a745;
        }

        .action-buttons {
            margin-top: 30px;
        }

        .action-buttons a {
            text-decoration: none;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }

        .action-buttons a:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            margin-top: 10px;
            font-size: 0.9em;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>スタッフ詳細</h1>

        <div class="detail-item">
            <span>名前:</span>
            <span>山田 太郎</span>
        </div>

        <div class="detail-item">
            <span>メールアドレス:</span>
            <span>yamada.taro@example.com</span>
        </div>

        <div class="detail-item password-section">
            <span>パスワード:</span>
            <span>●●●●●●●</span>
        </div>

        <div class="detail-item">
            <span>交通費:</span>
            <span>￥1000</span>
        </div>

        <div class="detail-item">
            <span>時給:</span>
            <span>￥1100</span>
        </div>

        <div class="detail-item">
            <span>在籍状況:</span>
            <span class="status status-active">在籍中</span>
        </div>

        <div class="action-buttons">
            <a href="{{ route('staffEdit') }}" class="button">編集する</a>
        </div>

        <a href="{{ route('staff') }}" class="button">スタッフ一覧に戻る</a>
    </div>
</body>
</html>
