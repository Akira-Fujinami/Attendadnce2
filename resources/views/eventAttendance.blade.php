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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .dropdown {
            margin-bottom: 20px;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
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

        .btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
    <h1>{{ $event->name ?? 'イベントごとの出勤簿' }}</h1>

    <!-- イベント関連のナビゲーションリンク -->
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="{{ route('events.create') }}" class="btn">イベントを作成</a>
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
                        <a href="{{ route('attendanceDetail', ['employeeId' => $employee->id,'year' => $event->fromDate, 'month' => $event->toDate, 'eventId' => $event->id]) }}" class="btn">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align: right;">総給与:</th>
                    <th>¥{{ number_format($totalSalary) }}</th>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>
</body>
</html>
