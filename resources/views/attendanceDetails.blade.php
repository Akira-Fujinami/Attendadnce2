<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>打刻詳細</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: #fff;
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

        tr.pending-row {
            background-color: #fff3cd;
        }

        tr.approved-row {
            background-color: #d4edda;
        }

        tr.rejected-row {
            background-color: #f8d7da;
        }

        .form-inline {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .form-inline input,
        .form-inline select {
            padding: 5px;
            font-size: 1em;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .action-buttons form {
            display: inline-block;
        }

        .save-btn {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
        }

        .save-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            padding: 8px 12px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm("本当に削除しますか？");
        }
    </script>

</head>
<body>
    <div class="container">
        <h1>打刻詳細: {{ $date }}</h1>

        <table>
            <thead>
                <tr>
                    <th>打刻</th>
                    <th>打刻種類</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aditRecords as $record)
                <tr class="{{ $record->status === 'pending' ? 'pending-row' : ($record->status === 'approved' ? 'approved-row' : ($record->status === 'rejected' ? 'rejected-row' : '')) }}">
                    <td>
                        <form method="POST" action="{{ route('attendance.update', ['id' => $record->id]) }}" class="form-inline">
                            @csrf
                            <input type="time" name="minutes" value="{{ \Carbon\Carbon::parse($record->minutes)->format('H:i') }}">
                            <select name="adit_item">
                                <option value="work_start" {{ $record->adit_item === 'work_start' ? 'selected' : '' }}>出勤</option>
                                <option value="break_start" {{ $record->adit_item === 'break_start' ? 'selected' : '' }}>休憩開始</option>
                                <option value="break_end" {{ $record->adit_item === 'break_end' ? 'selected' : '' }}>休憩終了</option>
                                <option value="work_end" {{ $record->adit_item === 'work_end' ? 'selected' : '' }}>退勤</option>
                            </select>
                    </td>
                    <td>
                        @switch($record->status)
                            @case('approved')
                                承認済み
                                @break
                            @case('pending')
                                承認待ち
                                @break
                            @case('rejected')
                                却下済み
                                @break
                            @default
                                不明
                        @endswitch
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button type="submit" class="save-btn">更新</button>
                        </form>
                        <form method="POST" action="{{ route('attendance.delete', ['id' => $record->id]) }}" onsubmit="return confirmDelete()">
                            @csrf
                            <button type="submit" class="delete-btn">削除</button>
                        </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <h2 style="text-align: center;">新規打刻を追加</h2>
        @if ($errors->any())
            <div class="error-messages">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li style="color: red;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('attendance.store') }}" class="form-inline" style="justify-content: center; margin-bottom: 20px;">
            @csrf
            <input type="time" name="minutes" required>
            <input type='hidden' name='date' value='{{$date}}'>
            <input type='hidden' name='employee' value='{{$employee->id}}'>
            <input type='hidden' name='company' value='{{$employee->company_id}}'>
            <select name="adit_item" required>
                <option value="work_start">出勤</option>
                <option value="break_start">休憩開始</option>
                <option value="break_end">休憩終了</option>
                <option value="work_end">退勤</option>
            </select>
            <button type="submit" class="save-btn">追加</button>
        </form>


        <div class="back-link">
            <a href="{{ route('attendanceList', ['companyId' => $employee->company_id]) }}">出勤簿一覧に戻る</a>
        </div>
    </div>
</body>
</html>
