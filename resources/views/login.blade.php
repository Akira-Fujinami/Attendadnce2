<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to bottom, #4A90E2, #007BFF);
        }

        .login-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            font-size: 1.8em;
            color: #007BFF;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #007BFF;
            color: white;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        .login-btn:hover {
            background: #0056b3;
        }

        .links {
            margin-top: 15px;
            font-size: 0.9em;
        }

        .links a {
            text-decoration: none;
            color: #007BFF;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>ログイン</h1>
        @if ($errors->has('email'))
            <div style="color: red;">{{ $errors->first('email') }}</div>
        @endif

        <form action="/login" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">メールアドレス:</label>
                <input type="email" id="email" name="email" value="{{ old('email', request()->cookie('email')) }}" placeholder="例: yamada@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" placeholder="パスワード" required>
            </div>
            <button type="submit" class="login-btn">ログイン</button>
        </form>
        <div class="links">
            <a href="{{ route('passwordReset') }}">パスワードをお忘れですか？</a><br>
            <a href="{{ route('register') }}">管理者の登録はこちらから</a>
        </div>
    </div>
</body>
</html>
