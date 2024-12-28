<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ編集画面</title>
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
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
        }

        .save-btn {
            background-color: #007bff;
            color: #fff;
        }

        .save-btn:hover {
            background-color: #0056b3;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: #fff;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
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
        <h1>スタッフ編集画面</h1>
        <form>
            <label for="name">名前</label>
            <input type="text" id="name" value="山田 太郎">

            <label for="email">メールアドレス</label>
            <input type="email" id="email" value="yamada.taro@example.com">

            <label for="password">パスワード</label>
            <input type="password" id="password" value="password123">

            <label for="name">交通費</label>
            <input type="text" id="name" value="￥1000">

            <label for="name">時給</label>
            <input type="text" id="name" value="￥1100">

            <label for="status">在籍状況</label>
            <select id="status">
                <option value="active" selected>在籍中</option>
                <option value="inactive">退職済み</option>
            </select>

            <div class="form-actions">
                <button type="submit" class="save-btn">保存する</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='{{ route('staffDetail') }}';">キャンセル</button>
            </div>
        </form>

        <div class="back-link">
            <a href="{{ route('staff') }}" class="button">スタッフ一覧に戻る</a>
        </div>
    </div>
</body>
</html>
