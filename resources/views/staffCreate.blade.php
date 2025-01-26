<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規スタッフ追加</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 90%; /* 幅を画面の90%に制限 */
            width: 400px; /* デフォルトの幅 */
            padding: 20px 30px;
            box-sizing: border-box; /* パディングを含めたサイズを調整 */
        }

        .form-container h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .error {
            color: red;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .button-group button {
            flex: 1; /* ボタンの幅を均等に設定 */
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin: 0 5px; /* ボタン間の間隔 */
        }

        .button-group .submit-btn {
            background: #007bff;
            color: white;
        }

        .button-group .cancel-btn {
            background: #6c757d;
            color: white;
        }

        .button-group button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>新規スタッフ追加</h1>
        <form action="{{ route('employeeCreate') }}" method="POST" style="display: inline;">
            @csrf
            <div class="form-group">
                <label for="name">名前:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="例: 山田 太郎">
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">メールアドレス:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="例: yamada@example.com">
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="transportation">交通費:</label>
                <input type="text" id="transportation" name="transportation" value="{{ old('transportation') }}" placeholder="例: 1000">
                @error('transportation')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="wage">時給:</label>
                <input type="text" id="wage" name="wage" value="{{ old('wage') }}" placeholder="例: 1100">
                @error('wage')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" placeholder="パスワードを入力">
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="status">在籍状況:</label>
                <select id="status" name="status">
                    <option value="在職中" {{ old('status') === '在職中' ? 'selected' : '' }}>在職中</option>
                    <option value="休職中" {{ old('status') === '休職中' ? 'selected' : '' }}>休職中</option>
                    <option value="退職済み" {{ old('status') === '退職済み' ? 'selected' : '' }}>退職</option>
                </select>
                @error('status')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="button-group">
                <button type="submit" class="submit-btn">追加する</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='{{ route('staff') }}'">キャンセル</button>
            </div>
        </form>
    </div>
</body>
</html>
