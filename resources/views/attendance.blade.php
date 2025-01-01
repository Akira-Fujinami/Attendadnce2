<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出勤簿</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
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
        .summary {
            text-align: right;
            font-size: 1.2em;
        }
        .summary p {
            margin: 0;
        }
        .return-button {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .return-button button {
            font-size: 1em;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .return-button button:hover {
            background-color: #0056b3;
        }
        .logout {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .logout form {
            display: inline;
        }

        .logout .button {
            font-size: 1em;
            padding: 10px 15px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout .button:hover {
            background-color: #c82333;
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
    <!-- 打刻画面へのリンク -->
    <div class="return-button">
        <button onclick="location.href='{{ route('adit') }}'">打刻画面に戻る</button>
    </div>
        <!-- ログアウトボタン -->
    <div class="logout">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="button">ログアウト</button>
        </form>
    </div>
    <h1>出勤簿</h1>
    <div class="info">
        <p><strong>スタッフ名:</strong> {{ $name }}</p>
        <p><strong>総労働時間:</strong> {{ number_format($totalWorkHours, 2) }} 時間</p>
        <p><strong>総休憩時間:</strong> {{ number_format($totalBreakHours, 2) }} 時間</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>労働時間</th>
                <th>休憩時間</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
                @php
                    $workStart = null;
                    $workEnd = null;
                    $breakStart = null;
                    $breakEnd = null;
                    
                    if (isset($attendanceRecords[$date])) {
                        $dailyRecords = collect($attendanceRecords[$date]);
                        $workStart = $dailyRecords->firstWhere('adit_item', 'work_start');
                        $workEnd = $dailyRecords->firstWhere('adit_item', 'work_end');
                        $breakStart = $dailyRecords->firstWhere('adit_item', 'break_start');
                        $breakEnd = $dailyRecords->firstWhere('adit_item', 'break_end');
                    }
                    $recordData = $attendanceRecords[$date] ?? null;
                @endphp
                <tr>
                    <td>
                        @if ($recordData && $recordData['has_pending'])
                            <span class="error-icon">&#33;</span>
                        @endif
                        <a href="{{ route('editAttendance', ['date' => $date, 'employeeId' => $employeeId]) }}" class="date-link">{{ $date }}</a>
                    </td>
                    <td>
                        @if($workStart)
                            {{ \Carbon\Carbon::parse($workStart->minutes)->format('H:i') }}
                        @endif
                    </td>
                    <td>
                        @if($workEnd)
                            {{ \Carbon\Carbon::parse($workEnd->minutes)->format('H:i') }}
                        @endif
                    </td>
                    <td>
                        @if($workStart && $workEnd)
                            @php
                                $totalMinutes = \Carbon\Carbon::parse($workStart->minutes)->diffInMinutes(\Carbon\Carbon::parse($workEnd->minutes));
                                $hours = floor($totalMinutes / 60);
                                
                                $minutes = $totalMinutes % 60;
                                $formattedHours = $hours + ($minutes / 100); // 分を60分率で計算
                            @endphp
                            {{ number_format($formattedHours, 2) }} 時間
                        @else
                            0.00 時間
                        @endif
                    </td>
                    <td>
                        @if($breakStart && $breakEnd)
                            @php
                                $totalMinutes = \Carbon\Carbon::parse($breakStart->minutes)->diffInMinutes(\Carbon\Carbon::parse($breakEnd->minutes));
                                $hours = floor($totalMinutes / 60);
                                
                                $minutes = $totalMinutes % 60;
                                $formattedHours = $hours + ($minutes / 100); // 分を60分率で計算
                            @endphp
                            {{ number_format($formattedHours, 2) }} 時間
                        @else
                            0.00 時間
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
