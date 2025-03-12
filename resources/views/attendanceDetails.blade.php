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
        .custom-select {
            width: 70%; /* 幅を調整 */
            padding: 8px;
            font-size: 1.1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            cursor: pointer;
        }
        .custom-select option {
            padding: 10px;
            font-size: 1em;
            background-color: #fff;
            color: #333;
        }
        .custom-select option:checked {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .select-container {
            display: flex;
            gap: 10px; /* 間隔を調整 */
            align-items: center;
        }



        @media screen and (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            .action-buttons {
                flex-wrap: nowrap; /* スマホでも横並びを維持 */
                justify-content: space-between;
            }

            .save-btn, .delete-btn {
                flex: 1;
                max-width: 48%; /* 画面幅の約半分 */
                min-width: 90px; /* 削れないように最小幅を確保 */
                font-size: 14px; /* 文字の視認性を維持 */
                white-space: nowrap; /* 文字の折り返しを防ぐ */
            }
        }


    </style>
    <script>
        function confirmDelete() {
            return confirm("本当に削除しますか？");
        }
        function updateHiddenEventId(selectElement) {
            let selectedOption = selectElement.options[selectElement.selectedIndex];
            let eventId = selectedOption.getAttribute('data-event-id');

            // すべての hidden_event_id を取得して更新
            document.querySelectorAll('input[name="event_id"]').forEach(hiddenInput => {
                hiddenInput.value = eventId;
            });
        }
    </script>

</head>
<body>
    <div class="container">
        @php
            $weekdays = ['Sun' => '日', 'Mon' => '月', 'Tue' => '火', 'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土'];
            $parsedDate = \Carbon\Carbon::parse($date);
            $Date = $parsedDate->format('Y/n/j') . ' (' . $weekdays[$parsedDate->format('D')] . ')';
        @endphp
        <h1>{{ $Date }}</h1>
        <form method="POST" action="{{ route('attendance.update.event', ['date' => $date]) }}" class="form-inline">
            @csrf
            <div class="select-container">
                <select name="event_item" class="custom-select" onchange="updateHiddenEventId(this)">
                <option value="" disabled {{ empty($eventSelected) ? 'selected' : '' }}>選択してください</option>
                    @foreach ($events as $eventItem)
                        <option value="{{ $eventItem->value }}" 
                            data-event-id="{{ $eventItem->id }}"
                            {{ isset($eventSelected) && $eventSelected->id == $eventItem->id ? 'selected' : '' }}>
                            {{ $eventItem->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="event_id" id="hidden_event_id" value="{{ $eventSelected->id ?? '' }}">
                <button type="submit" class="save-btn">保存</button>
            </div>
        </form>

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
                            <input type="hidden" name="event_id" value="{{ $eventSelected->id ?? '' }}">
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
                            <input type="hidden" name="event_id" value="{{ $eventSelected->id ?? '' }}">
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
            <input type="hidden" name="event_id" id="hidden_event_id" value="{{ $eventSelected->id ?? '' }}">
            <button type="submit" class="save-btn">追加</button>
        </form>


        <div class="back-link">
            <a href="{{ route('attendanceList', ['companyId' => $employee->company_id]) }}">出勤簿一覧に戻る</a>
        </div>
    </div>
</body>
</html>
