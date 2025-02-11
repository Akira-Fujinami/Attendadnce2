<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベントごとの出勤簿</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .excel-export-form {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-align: center;
        }

        .btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .excel-export-button {
            padding: 15px 30px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0 auto 20px;
            width: fit-content;
        }

        .excel-export-button:hover {
            background-color: #218838;
            transform: translateY(-2px);
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
            background: #007bff;
            color: white;
        }

        .text-right {
            text-align: right;
        }

        .dropdown {
            display: flex;
            justify-content: center; /* 中央寄せ */
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
        }

        .dropdown form {
            width: 100%;
            max-width: 400px; /* 適度な幅を設定 */
        }

        .dropdown select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            background-color: white;
            cursor: pointer;
        }

        /* フォーカス時のスタイル */
        .dropdown select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $event->name ?? 'イベントごとの出勤簿' }}</h1>

        <!-- エクセル出力ボタン -->
        <form action="{{ route('eventAttendance.export') }}" method="POST" class="excel-export-form">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id ?? '' }}">
            <input type="hidden" name="company_id" value="{{ Auth::user()->id }}">
            <button type="submit" class="excel-export-button" {{ empty($event) ? 'disabled' : '' }}>
                エクセルを出力
            </button>
        </form>

        <!-- ボタン一覧 -->
        <div class="button-container">
            <a href="{{ route('staff') }}" class="btn btn-secondary">⬅ スタッフ一覧へ戻る</a>
            <a href="{{ route('events.create') }}" class="btn">イベントを作成</a>
            <a href="{{ route('events.index') }}" class="btn">イベント一覧</a>
        </div>

        <!-- イベント選択 -->
        <div class="dropdown">
            <form action="{{ route('events.show') }}" method="GET">
                <select name="event_id" onchange="this.form.submit()">
                    <option value="" disabled selected>イベントを選択してください</option>
                    @foreach ($allEvents as $ev)
                        <option value="{{ $ev->id }}" {{ $event && $event->id == $ev->id ? 'selected' : '' }}>
                            {{ $ev->name }} ({{ $ev->fromDate }} ～ {{ $ev->toDate }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($event)
        <table>
            <thead>
                <tr>
                    <th>従業員名</th>
                    <th>出勤日数</th>
                    <th>労働時間</th>
                    <th>給与</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->attendanceDays }}</td>
                    <td>
                        @php
                            $totalMinutes = $employee->totalWorkHours * 60;
                            $hours = floor($totalMinutes / 60);
                            $minutes = $totalMinutes % 60;
                        @endphp
                        {{ sprintf('%02d時間%02d分', $hours, $minutes) }}
                    </td>
                    <td>¥{{ number_format($employee->totalSalary) }}</td>
                    <td>
                        <a href="{{ route('attendanceDetail', ['employeeId' => $employee->id, 'year' => $event->fromDate, 'month' => $event->toDate, 'eventId' => $event->id]) }}" class="btn">
                            詳細
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">総給与:</th>
                    <th>¥{{ number_format($totalSalary) }}</th>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>
</body>
</html>
