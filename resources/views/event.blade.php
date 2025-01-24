<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント入力フォーム</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            margin: 0;
            padding: 20px;
            box-sizing: border-box; /* 全体でボックスサイズを調整 */
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            box-sizing: border-box; /* コンテナ内でボックスサイズを調整 */
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%; /* 親要素の幅にフィット */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box; /* パディングやボーダーを含めて幅を計算 */
        }

        .form-group textarea {
            height: 100px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .submit-btn {
            display: inline-block;
            width: 100%; /* ボタンを親要素の幅に合わせる */
            padding: 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            border: none;
            box-sizing: border-box; /* パディングを含めて幅を調整 */
        }

        .submit-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .submit-btn:active {
            background-color: #1e7e34;
            transform: translateY(0);
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>イベント入力フォーム</h1>
        @if ($errors->any())
            <div style="color: red; margin-bottom: 20px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('events.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">イベント名:</label>
                <input type="text" id="name" name="name" placeholder="例: 社内ミーティング" required>
            </div>
            <div class="form-group">
                <label for="start_date">開始日付:</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">終了日付:</label>
                <input type="date" id="end_date" name="end_date" required>
            </div>
            <div class="form-group">
                <label for="description">説明:</label>
                <textarea id="description" name="description" placeholder="例: 社内の重要な会議やイベントの説明"></textarea>
            </div>
            <button type="submit" class="submit-btn">保存</button>
        </form>
    </div>
</body>
</html>
