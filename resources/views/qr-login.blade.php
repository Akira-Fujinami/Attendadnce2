<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRコードログイン</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        h1 {
            color: #007bff;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            width: 90%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loginForm = document.getElementById("login-form");
            const loginButton = document.getElementById("login-btn");

            loginForm.addEventListener("submit", function(event) {
                loginButton.disabled = true; // ボタンを無効化
                loginButton.textContent = "処理中..."; // ログイン中のメッセージに変更
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>QRコードログイン</h1>
        <p>イベント: <strong>{{ $event->name }}</strong></p>
        <form method="POST" action="{{ route('qr.login.post') }}" id="login-form">
            @csrf
            <input type="hidden" name="event_id" value="{{ $eventId }}">

            <label for="email">メールアドレス:</label>
            <input type="email" name="email" id="email" required value="{{ session('saved_email', '') }}">

            <label for="password">パスワード:</label>
            <input type="password" name="password" id="password" required value="{{ session('saved_password', '') }}">

            <button type="submit" class="btn" id ="login-btn">ログイン</button><br>
            <a href="{{ route('passwordReset') }}">パスワードをお忘れですか？</a>
        </form>
    </div>
</body>
</html>
