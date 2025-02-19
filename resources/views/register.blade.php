<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規スタッフ登録</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 400px;
            background: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            color: #fff;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .error-box {
            border: 2px solid #ff4d4d; /* 赤い枠 */
            background-color: #ffe6e6; /* 薄い赤背景 */
            color: #cc0000; /* 濃い赤のテキスト */
            padding: 15px;
            border-radius: 5px; /* 角を丸くする */
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error-list {
            list-style-type: none; /* デフォルトのリストスタイルを削除 */
            padding: 0;
            margin: 0;
        }

        .error-list li {
            padding: 5px 0;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>新規登録</h1>
        @if ($errors->any())
            <div class="alert alert-danger error-box">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST">
        @csrf
            <label for="name">名前:</label>
            <input type="text" id="name" name="name" placeholder="例: 山田 太郎" required>

            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email" placeholder="例: yamada@example.com" required>

            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" placeholder="パスワードを入力" required>

            <label for="password_confirmation">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>

            <div class="btn-container">
                <button type="submit" class="btn btn-primary">登録</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('login') }}';">キャンセル</button>
            </div>
        </form>
    </div>
</body>
</html>
