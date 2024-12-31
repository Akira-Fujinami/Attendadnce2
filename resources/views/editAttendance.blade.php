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
            font-size: 1em;
            margin-bottom: 5px;
        }

        input[type="time"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
        }

        .save-btn {
            background-color: #007bff;
            color: white;
        }

        .save-btn:hover {
            background-color: #0056b3;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>打刻修正画面</h1>
        <form method="POST" action="{{ route('updateAttendance') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">

            <label for="work_start">出勤時間</label>
            <input type="time" id="work_start" name="work_start" 
                   value="{{ $attendanceRecords->where('adit_item', 'work_start')->first() ? \Carbon\Carbon::parse($attendanceRecords->where('adit_item', 'work_start')->first()->minutes)->format('H:i') : '' }}">

            <label for="work_end">退勤時間</label>
            <input type="time" id="work_end" name="work_end" 
                   value="{{ $attendanceRecords->where('adit_item', 'work_end')->first() ? \Carbon\Carbon::parse($attendanceRecords->where('adit_item', 'work_end')->first()->minutes)->format('H:i') : '' }}">

            <label for="break_start">休憩開始時間</label>
            <input type="time" id="break_start" name="break_start" 
                   value="{{ $attendanceRecords->where('adit_item', 'break_start')->first() ? \Carbon\Carbon::parse($attendanceRecords->where('adit_item', 'break_start')->first()->minutes)->format('H:i') : '' }}">

            <label for="break_end">休憩終了時間</label>
            <input type="time" id="break_end" name="break_end" 
                   value="{{ $attendanceRecords->where('adit_item', 'break_end')->first() ? \Carbon\Carbon::parse($attendanceRecords->where('adit_item', 'break_end')->first()->minutes)->format('H:i') : '' }}">

            <button type="submit" class="save-btn">保存</button>
            <button type="button" class="cancel-btn" onclick="window.history.back()">キャンセル</button>
        </form>
    </div>
</body>
</html>
