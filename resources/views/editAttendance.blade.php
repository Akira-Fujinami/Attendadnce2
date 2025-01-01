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
            bottom: 120%; /* ビックリマークの上に表示 */
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            white-space: nowrap;
            display: none; /* デフォルトは非表示 */
            color: red;
        }

        .error-icon:hover::after {
            display: block; /* ホバー時に表示 */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>打刻修正画面</h1>

        <table>
            <thead>
                <tr>
                    <th>項目</th>
                    <th>修正前</th>
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

                @foreach (['work_start', 'work_end', 'break_start', 'break_end'] as $aditItem)
                    <tr>
                        <td>
                            @if ($currentRecord && $currentRecord->adit_item === $aditItem && $currentRecord->status === 'pending')
                                <span class="error-icon">&#33;</span>
                            @endif
                            {{ $labels[$aditItem] }}
                        </td>
                        <td>
                            @if ($previousRecord && $previousRecord->adit_item === $aditItem)
                                {{ \Carbon\Carbon::parse($previousRecord->minutes)->format('H:i') }}
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
                                    value="{{ $currentRecord && $currentRecord->adit_item === $aditItem ? \Carbon\Carbon::parse($currentRecord->minutes)->format('H:i') : '' }}">
                                <button type="submit" class="save-btn">保存</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="cancel-btn" onclick="window.history.back()">キャンセル</button>
    </div>
</body>
</html>
