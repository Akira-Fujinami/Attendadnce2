<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カレンダー</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f4f7f6;
        }
        .calendar-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        td a {
            text-decoration: none;
            color: #007bff;
        }
        td a:hover {
            text-decoration: underline;
        }
        .month-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .month-navigation a {
            text-decoration: none;
            font-size: 4em; /* 矢印のサイズを大きくする */
            color: #007bff;
            font-weight: bold;
            padding: 10px 20px; /* クリックしやすくするための余白 */
            border-radius: 50%; /* 丸みをつける */
            background-color: #f0f8ff; /* 背景色を追加 */
            transition: background-color 0.3s ease, transform 0.2s ease; /* ホバー効果 */
        }
        .month-navigation a:hover {
            background-color: #d6e4ff;
            transform: scale(1.1); /* ホバー時に少し拡大 */
        }
        .month-header {
            font-size: 1.5em;
            font-weight: bold;
            color: #007bff;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        @php
            // 選択された日付
            $selectedDate = Carbon\Carbon::parse($selectedDate);

            // 前月と翌月を計算
            $previousMonth = $selectedDate->copy()->subMonth();
            $nextMonth = $selectedDate->copy()->addMonth();

            // 現在の月のフォーマット
            $currentMonth = $selectedDate->format('Y年 n月');

            // 月初と月末
            $startOfMonth = $selectedDate->copy()->startOfMonth();
            $endOfMonth = $selectedDate->copy()->endOfMonth();

            // カレンダー範囲
            $startOfWeek = $startOfMonth->copy()->startOfWeek(Carbon\Carbon::SUNDAY);
            $endOfWeek = $endOfMonth->copy()->endOfWeek(Carbon\Carbon::SATURDAY);
        @endphp

        <!-- 月ナビゲーション -->
        <div class="month-navigation">
            <a href="{{ route('showCalendar', ['date' => $previousMonth->toDateString()]) }}">&#8592;</a>
            <div class="month-header">{{ $currentMonth }}</div>
            <a href="{{ route('showCalendar', ['date' => $nextMonth->toDateString()]) }}">&#8594;</a>
        </div>

        <!-- カレンダー表示 -->
        <table>
            <thead>
                <tr>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                </tr>
            </thead>
            <tbody>
                @for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay())
                    @if ($date->dayOfWeek == Carbon\Carbon::SUNDAY) <tr> @endif
                    <td>
                        @if ($date->month == $startOfMonth->month)
                            <!-- 月内の日付 -->
                            <a href="{{ route('attendance.daily', ['date' => $date->toDateString()]) }}">{{ $date->day }}</a>
                        @else
                            <!-- 他の月の日付 -->
                            <span style="color: #ccc;">{{ $date->day }}</span>
                        @endif
                    </td>
                    @if ($date->dayOfWeek == Carbon\Carbon::SATURDAY) </tr> @endif
                @endfor
            </tbody>
        </table>
        <div class="back-link">
            <a href="{{ route('staff') }}">スタッフ一覧に戻る</a>
        </div>
    </div>
</body>
</html>
