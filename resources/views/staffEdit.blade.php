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
        <form action="{{ route('staffUpdate', ['id' => $employee->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ $employee->name }}">

            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ $employee->email }}">

            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" placeholder="パスワードを変更する場合入力">

            <label for="transportation_fee">交通費</label>
            <input type="text" id="transportation_fee" name="transportation_fee" value="{{ $employee->transportation_fee }}">

            <label for="hourly_wage">時給</label>
            <input type="text" id="hourly_wage" name="hourly_wage" value="{{ $employee->hourly_wage }}">

            <label for="retired">在籍状況</label>
            <select id="retired" name="retired">
                <option value="在職中" {{ $employee->retired === '在職中' ? 'selected' : '' }}>在職中</option>
                <option value="休職中" {{ $employee->retired === '休職中' ? 'selected' : '' }}>休職中</option>
                <option value="退職済み" {{ $employee->retired === '退職済み' ? 'selected' : '' }}>退職済み</option>
            </select>

            <div class="form-actions">
                <button type="submit" class="save-btn">保存する</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='{{ route('staffDetail', ['id' => $employee->id]) }}';">キャンセル</button>
            </div>
        </form>

        <div class="back-link">
            <a href="{{ route('staff') }}">スタッフ一覧に戻る</a>
        </div>
    </div>
</body>
</html>
