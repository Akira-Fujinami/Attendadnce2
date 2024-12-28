<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>打刻修正画面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 500px;
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

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .form-group input[readonly] {
            background-color: #f8f9fa;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            cursor: pointer;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>打刻修正画面</h1>
        <div class="form-group">
            <label for="date">日付</label>
            <input type="text" id="date" value="2024年12月1日" readonly>
        </div>
        <div class="form-group">
            <label for="clock-in">出勤時間</label>
            <input type="time" id="clock-in" value="09:00">
        </div>
        <div class="form-group">
            <label for="break-start">休憩開始時間</label>
            <input type="time" id="break-start" value="12:00">
        </div>
        <div class="form-group">
            <label for="break-end">休憩終了時間</label>
            <input type="time" id="break-end" value="13:00">
        </div>
        <div class="form-group">
            <label for="clock-out">退勤時間</label>
            <input type="time" id="clock-out" value="18:00">
        </div>
        <button class="submit-btn">修正を保存する</button>
    </div>
</body>
</html>
