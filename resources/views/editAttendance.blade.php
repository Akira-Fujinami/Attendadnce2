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
    <h2 style="text-align: center; margin-bottom: 20px;">{{ $year }}年 {{ $month }}月 {{ $day }}日</h2> 
        <h1>打刻修正画面</h1>
        @if(!$disable)
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

        // `break_start` と `break_end` をまとめて時間順に並べ替える
        $combinedRecords = collect(array_merge(
            $records['break_start']['previousRecord'] ?? [],
            $records['break_end']['previousRecord'] ?? []
        ))
        ->unique(fn($item) => $item->id) // IDで重複排除
        ->sortBy(fn($item) => \Carbon\Carbon::parse($item->minutes))
        ->values();

    @endphp

    {{-- 出勤・退勤 --}}
    @foreach ($records as $aditItem => $record)
        @if (in_array($aditItem, ['work_start']))
            <tr>
                <td>
                    @if (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                        <span class="error-icon">&#33;</span>
                    @endif
                    {{ $labels[$aditItem] }}
                </td>
                <td>
                    @if (!empty($record['previousRecord']))
                        {{ \Carbon\Carbon::parse($record['previousRecord'][0]->minutes)->format('H:i') }}
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
                            value="{{ !empty($record['currentRecord']) ? \Carbon\Carbon::parse($record['currentRecord'][0]->minutes)->format('H:i') : '' }}">
                        <button type="submit" class="save-btn">保存</button>
                    </form>
                </td>
            </tr>
        @endif
    @endforeach

    {{-- 休憩関連のデータ --}}
    @if ($combinedRecords->isNotEmpty())
        @foreach ($combinedRecords as $breakRecord)
            @php
            $matchingCurrentRecord = collect(array_merge(
                $records['break_start']['currentRecord'] ?? [],
                $records['break_end']['currentRecord'] ?? []
            ))->firstWhere('before_adit_id', $breakRecord->id);
            @endphp
            <tr>
                <td>
                    @if ($matchingCurrentRecord)
                        <span class="error-icon">&#33;</span>
                    @endif
                    {{ $breakRecord->adit_item === 'break_start' ? '休憩開始' : '休憩終了' }}
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($breakRecord->minutes)->format('H:i') }}
                </td>
                <td>
                    <form method="POST" action="{{ route('updateAttendance') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                        <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                        <input type="hidden" name="adit_item" value="{{ $breakRecord->adit_item }}">
                        <input type="hidden" name="adit_id" value="{{ $breakRecord->id }}">
                        <input type="time" name="{{ $breakRecord->adit_item }}"
                            value="{{ $matchingCurrentRecord ? \Carbon\Carbon::parse($matchingCurrentRecord->minutes)->format('H:i') : '' }}">
                        <button type="submit" class="save-btn">保存</button>
                    </form>
                </td>
            </tr>
        @endforeach
    @else
        @foreach ($records as $aditItem => $record)
            @if (in_array($aditItem, ['break_start']))
                <tr>
                    <td>
                        @if (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                            <span class="error-icon">&#33;</span>
                        @endif
                        {{ $labels['break_start'] }}
                    </td>
                    <td>
                        未登録
                    </td>
                    <td>
                        <form method="POST" action="{{ route('updateAttendance') }}">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ 'break_start' }}">
                            <input type="time" name="{{ 'break_start' }}"
                                value="{{ !empty($record['currentRecord']) ? \Carbon\Carbon::parse($record['currentRecord'][0]->minutes)->format('H:i') : '' }}">
                            <button type="submit" class="save-btn">保存</button>
                        </form>
                    </td>
                </tr>
            @endif
        @endforeach
        @foreach ($records as $aditItem => $record)
            @if (in_array($aditItem, ['break_end']))
                <tr>
                    <td>
                        @if (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                            <span class="error-icon">&#33;</span>
                        @endif
                        {{ $labels['break_end'] }}
                    </td>
                    <td>
                        未登録
                    </td>
                    <td>
                        <form method="POST" action="{{ route('updateAttendance') }}">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ 'break_end' }}">
                            <input type="time" name="{{ 'break_end' }}"
                                value="{{ !empty($record['currentRecord']) ? \Carbon\Carbon::parse($record['currentRecord'][0]->minutes)->format('H:i') : '' }}">
                            <button type="submit" class="save-btn">保存</button>
                        </form>
                    </td>
                </tr>
            @endif
        @endforeach
    @endif
    @foreach ($records as $aditItem => $record)
        @if (in_array($aditItem, ['work_end']))
            <tr>
                <td>
                    @if (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                        <span class="error-icon">&#33;</span>
                    @endif
                    {{ $labels[$aditItem] }}
                </td>
                <td>
                    @if (!empty($record['previousRecord']))
                        {{ \Carbon\Carbon::parse($record['previousRecord'][0]->minutes)->format('H:i') }}
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
                            value="{{ !empty($record['currentRecord']) ? \Carbon\Carbon::parse($record['currentRecord'][0]->minutes)->format('H:i') : '' }}">
                        <button type="submit" class="save-btn">保存</button>
                    </form>
                </td>
            </tr>
        @endif
    @endforeach
</tbody>


        </table>
        @elseif ($disable)
        <div style="text-align: center; margin: 20px 0; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">
            <strong>この日付の打刻データは未来日の為、修正できません。</strong>
        </div>
        @endif


        <div class="navigation">
        <a href="{{ route('attendance', ['company_id' => Auth::user()->company_id, 'employee_id' => Auth::user()->id]) }}" class="button">出勤簿へ遷移</a>
    </div>
    </div>
</body>
</html>
