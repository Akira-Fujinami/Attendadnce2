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
            padding: 7px;
            margin-bottom: 10px;
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

        .error {
            color: red;
            font-size: 0.85em;
            margin-top: -10px;
            margin-bottom: 10px;
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
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 55%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2em;
            color: #888;
        }

        .toggle-password:hover {
            color: #007BFF;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
            position: relative;
        }
    </style>
    <script>
        function togglePassword() {
            let passwordField = document.getElementById("password");
            let toggleIcon = document.querySelector(".toggle-password");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.textContent = "🙈"; // アイコンを変更（目を閉じた絵文字）
            } else {
                passwordField.type = "password";
                toggleIcon.textContent = "👁️"; // アイコンを変更（目の絵文字）
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>スタッフ編集画面</h1>
        <form action="{{ route('staffUpdate', ['id' => $employee->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name', $employee->name) }}">
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email', $employee->email) }}">
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" placeholder="パスワードを変更する場合入力">
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <label for="transportation_fee">交通費</label>
            <input type="text" id="transportation_fee" name="transportation_fee" value="{{ old('transportation_fee', $employee->transportation_fee) }}">
            @error('transportation_fee')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="hourly_wage">時給</label>
            <input type="text" id="hourly_wage" name="hourly_wage" value="{{ old('hourly_wage', $employee->hourly_wage) }}">
            @error('hourly_wage')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="retired">在籍状況</label>
            <select id="retired" name="retired">
                <option value="在職中" {{ old('retired', $employee->retired) === '在職中' ? 'selected' : '' }}>在職中</option>
                <option value="休職中" {{ old('retired', $employee->retired) === '休職中' ? 'selected' : '' }}>休職中</option>
                <option value="退職済み" {{ old('retired', $employee->retired) === '退職済み' ? 'selected' : '' }}>退職済み</option>
            </select>
            @error('retired')
                <p class="error">{{ $message }}</p>
            @enderror

            <div class="form-actions">
                <button type="submit" class="save-btn">保存する</button>
            </div>
        </form>

        <div class="back-link">
            <a href="{{ route('staff') }}">スタッフ一覧に戻る</a>
        </div>
    </div>
</body>
</html>
