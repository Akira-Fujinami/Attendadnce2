<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント一覧</title>
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
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .event-list {
            list-style: none;
            padding: 0;
        }

        .event-item {
            background: #ffffff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .event-details {
            flex-grow: 1;
            min-width: 60%;
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }

        .event-dates {
            color: #666;
            font-size: 0.9em;
        }

        .event-description {
            margin-top: 10px;
            color: #444;
            font-size: 1em;
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1em;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #dc3545;
            border: none;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .qr-btn {
            background-color: #28a745;
        }

        .qr-btn:hover {
            background-color: #218838;
        }

        .btn-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            width: 100%;
            margin-bottom: 20px;
        }


        @media screen and (max-width: 768px) {
            .btn-container {
                width: 100%;
                text-align: right;
                margin-top: 10px;
            }

            .btn-container {
                display: flex;
                justify-content: flex-end;
                width: 100%;
            }
            .btn-group {
                flex-wrap: nowrap; /* 折り返し禁止 */
                justify-content: space-between; /* 均等配置 */
            }
            .btn {
                flex: 1;
                max-width: 48%; /* 画面幅の約半分に調整 */
                text-align: center;
                white-space: nowrap; /* テキスト折り返し禁止 */
                font-size: 13px; /* フォントサイズを小さく調整 */
            }
            .event-item {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }

            .delete-btn {
                width: 100%;
                max-width: 120px;
                padding: 10px 15px;
                font-size: 14px;
                text-align: center;
            }
        }

    </style>
    <script>
        function confirmDelete(eventId) {
            if (confirm("本当にこのイベントを削除しますか？")) {
                document.getElementById('delete-form-' + eventId).submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>イベント一覧</h1>

        <div class="btn-group">
            <a href="{{ route('events.create') }}" class="btn">➕ イベントを追加</a>
            <a href="{{ route('events.show') }}" class="btn">🎉 イベント出勤簿</a>
        </div>
        @if ($events->isEmpty())
            <p style="text-align: center; color: #777;">イベントが登録されていません。</p>
        @else
            <ul class="event-list">
                @foreach ($events as $event)
                    @php
                        $weekdays = ['Sun' => '日', 'Mon' => '月', 'Tue' => '火', 'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土'];
                        $parsedFromDate = \Carbon\Carbon::parse($event->fromDate);
                        $fromDate = $parsedFromDate->format('Y/n/j') . ' (' . $weekdays[$parsedFromDate->format('D')] . ')';
                        $parsedToDate = \Carbon\Carbon::parse($event->toDate);
                        $toDate = $parsedToDate->format('Y/n/j') . ' (' . $weekdays[$parsedToDate->format('D')] . ')';
                    @endphp
                    <li class="event-item">
                        <div class="event-details">
                            <div class="event-header">
                                <span class="event-name">{{ $event->name }}</span>
                                <span class="event-dates">{{ $fromDate }} ～ {{ $toDate }}</span>
                            </div>
                            <div class="event-description">
                                {{ $event->description ?? '説明がありません' }}
                            </div>
                        </div>
                        <div class="btn-container">
                            <a href="{{ route('events.qr', ['event' => $event->id]) }}" class="btn qr-btn">📷 QRコード表示</a>
                            <form id="delete-form-{{ $event->id }}" method="POST" action="{{ route('events.delete', ['event' => $event->id]) }}">
                                @csrf
                                <button type="button" class="btn delete-btn" onclick="confirmDelete({{ $event->id }})">🗑 削除</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</body>
</html>
