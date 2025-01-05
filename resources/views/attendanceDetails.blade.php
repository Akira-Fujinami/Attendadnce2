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
            background-color: #fff3cd; /* 承認待ちの背景色（薄い黄色） */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>打刻詳細: {{ $date }}</h1>

        <table>
            <thead>
                <tr>
                    <th>時刻</th>
                    <th>打刻種類</th>
                    <th>ステータス</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aditRecords as $record)
                <tr class="{{ $record->status === 'pending' ? 'pending-row' : '' }}">
                    <td>{{ \Carbon\Carbon::parse($record->minutes)->format('H:i') }}</td>
                    <td>
                        @switch($record->adit_item)
                            @case('work_start')
                                出勤
                                @break
                            @case('break_start')
                                休憩開始
                                @break
                            @case('break_end')
                                休憩終了
                                @break
                            @case('work_end')
                                退勤
                                @break
                            @default
                                その他
                        @endswitch
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

                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="back-link">
            <a href="{{ route('attendanceList', ['companyId' => $employee->company_id]) }}">出勤簿一覧に戻る</a>
        </div>
    </div>
</body>
</html>
