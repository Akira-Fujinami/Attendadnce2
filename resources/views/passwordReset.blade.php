<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワードをリセット</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to bottom, #007bff, #00d4ff);
        }

        .reset-container {
            background: white;
            width: 90%;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .reset-container h1 {
            font-size: 1.8em;
            color: #007bff;
            margin-bottom: 20px;
        }

        .reset-container input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .reset-container button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .reset-container button:hover {
            background: #0056b3;
        }

        .reset-container a {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
            font-size: 0.9em;
        }

        .reset-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h1>パスワードをリセット</h1>
        <form action="/password-reset" method="POST">
            <input type="email" name="email" placeholder="登録されたメールアドレス" required>
            <button type="submit">リセットリンクを送信</button>
        </form>
        <a href="/login">ログイン画面に戻る こちら</a>
    </div>
</body>
</html>
