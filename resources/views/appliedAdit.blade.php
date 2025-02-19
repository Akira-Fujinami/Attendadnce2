<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>未承認打刻一覧</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        h2 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
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

        form {
            display: inline-block;
            margin: 0;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>未承認打刻一覧</h1>

        <div class="filter-dropdown" style="text-align: center; margin-bottom: 20px;">
            <form action="{{ route('appliedAdit', ['companyId' => Auth::user()->id]) }}" method="GET" id="filterForm">
                <select name="status" id="statusFilter" onchange="document.getElementById('filterForm').submit()" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>未承認</option>
                    <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>却下済み</option>
                </select>
            </form>
        </div>

        @if (!empty($pendingRecords))
            @foreach ($pendingRecords as $employeeName => $records)
                <h2>{{ $employeeName }}</h2>
                <table>
                    <thead>
                        <tr>
                            <th>名前</th>
                            <th>修正前の時間</th>
                            <th>修正後の時間</th>
                            <th>打刻項目</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                        <tr>
                            <td>{{ $record['name'] }}</td>
                            <td>
                                @if ($record['previous_time'] !== 'なし')
                                    {{ \Carbon\Carbon::parse($record['previous_time'])->format('H:i') }}
                                @else
                                    {{ $record['previous_time'] }}
                                @endif
                            </td>
                            <td>    
                                @if ($record['current_time'] !== '削除')
                                    {{ \Carbon\Carbon::parse($record['current_time'])->format('H:i') }}
                                @else
                                    {{ $record['current_time'] }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $aditLabels = [
                                        'work_start' => '出勤',
                                        'break_start' => '休憩開始',
                                        'break_end' => '休憩終了',
                                        'work_end' => '退勤',
                                    ];
                                @endphp
                                {{ $aditLabels[$record['adit_item']] ?? '不明な項目' }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('adit.approve') }}" style="margin-bottom: 5px;">
                                    @csrf
                                    <input type="hidden" name="company_id" value="{{ Auth::user()->id }}">
                                    <input type="hidden" name="employee_id" value="{{ $record['id'] }}">
                                    <input type="hidden" name="date" value="{{ $record['date'] }}">
                                    <input type="hidden" name="minutes" value="{{ $record['minutes'] }}">
                                    <input type="hidden" name="adit_id" value="{{ $record['adit_id'] }}">
                                    <input type="hidden" name="adit_item" value="{{ $record['adit_item'] }}">
                                    <input type="hidden" name="before_adit_id" value="{{ $record['adit_id'] }}">
                                    <input type="hidden" name="wage" value="{{ $record['hourly_wage'] }}">
                                    <input type="hidden" name="transportation" value="{{ $record['transportation_fee'] }}">
                                    <button type="submit" class="btn btn-approve">承認</button>
                                </form>
                                @if($currentStatus != 'rejected')
                                <form method="POST" action="{{ route('adit.reject') }}">
                                    @csrf
                                    <input type="hidden" name="company_id" value="{{ Auth::user()->id }}">
                                    <input type="hidden" name="employee_id" value="{{ $record['id'] }}">
                                    <input type="hidden" name="date" value="{{ $record['date'] }}">
                                    <input type="hidden" name="minutes" value="{{ $record['minutes'] }}">
                                    <input type="hidden" name="adit_item" value="{{ $record['adit_item'] }}">
                                    <input type="hidden" name="before_adit_id" value="{{ $record['adit_id'] }}">
                                    <button type="submit" class="btn btn-reject">却下</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @else
            @if($currentStatus == 'rejected')
            <p>却下済みの打刻はありません。</p>
            @elseif($currentStatus == 'pending')
                <p>未承認の打刻はありません。</p>
            @endif
        @endif

        <a href="{{ route('top') }}" class="back-button">トップに戻る</a>
    </div>
</body>
</html>
