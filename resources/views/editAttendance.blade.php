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
            max-width: 800px;
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
            color: white;
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

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }

        .error-icon {
            color: #dc3545;
            font-weight: bold;
            margin-left: 5px;
            font-size: 1.2em;
            position: relative;
            cursor: pointer;
        }

        .error-icon::after {
            content: "未承認の打刻です";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 120%;
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            white-space: nowrap;
            display: none;
        }

        .error-icon:hover::after {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
    <h2 style="text-align: center; margin-bottom: 20px;">{{ $year }}年 {{ $month }}月</h2> 
        <h1>打刻修正画面</h1>
        <table>
            <thead>
                <tr>
                    <th>項目</th>
                    <th>確定済みの打刻</th>
                    <th>修正後</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $labels = [
                        'work_start' => '出勤時間',
                        'work_end' => '退勤時間',
                        'break_start' => '休憩開始',
                        'break_end' => '休憩終了',
                    ];
                @endphp

                @foreach ($records as $aditItem => $record)
                    <tr>
                        <td>
                            @if ($record['currentRecord'] && $record['currentRecord']->status === 'pending')
                                <span class="error-icon">&#33;</span>
                            @endif
                            {{ $labels[$aditItem] }}
                        </td>
                        <td>
                            @if ($record['previousRecord'])
                                {{ \Carbon\Carbon::parse($record['previousRecord']->minutes)->format('H:i') }}
                            @else
                                未登録
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('updateAttendance') }}">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                                <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                                <input type="hidden" name="adit_item" value="{{ $aditItem }}">
                                <input type="time" name="{{ $aditItem }}" 
                                    value="{{ $record['currentRecord'] ? \Carbon\Carbon::parse($record['currentRecord']->minutes)->format('H:i') : '' }}">
                                <button type="submit" class="save-btn">保存</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="navigation">
        <a href="{{ route('attendance', ['company_id' => Auth::user()->company_id, 'employee_id' => Auth::user()->id]) }}" class="button">出勤簿へ遷移</a>
    </div>
    </div>
</body>
</html>
