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

        .error-icon-deleted {
            color: #dc3545;
            font-weight: bold;
            margin-left: 5px;
            font-size: 1.2em;
            position: relative;
            cursor: pointer;
        }

        .error-icon-deleted::after {
            content: "削除待ちの打刻です";
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

        .error-icon-deleted:hover::after {
            display: block;
        }
        .add-break-section {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        .add-break-section h2 {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #333;
        }
        .form-inline {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .form-inline input[type="time"] {
            padding: 5px;
            font-size: 1em;
            text-align: center;
        }

        .form-inline button {
            padding: 8px 12px;
            font-size: 0.9em;
        }
         /* エラー行の通常状態 */
        .error-row {
            background-color: #ffeb3b; /* ピンク背景 */
            position: relative;
            transition: background-color 0.3s ease;
        }

        /* ホバー時に背景色を強調 */
        .error-row:hover {
            background-color: #fff9c4; /* より濃いピンク */
            box-shadow: 0px 0px 15px rgba(255, 241, 118, 0.75);
        }

        /* ツールチップのデザイン */
        .error-tooltip {
            position: absolute;
            visibility: hidden;
            width: 220px;
            background-color: rgba(0, 0, 0, 0.85);
            color: #fff;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            bottom: 110%; /* 上に表示 */
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
            font-size: 0.9em;
            z-index: 10;
        }

        /* 行にカーソルを合わせた際にツールチップを表示 */
        .error-row:hover .error-tooltip {
            visibility: visible;
            opacity: 1;
            transform: translateX(-50%) translateY(-5px);
        }

        /* エラーアイコンの点滅 */
        @keyframes blink {
            50% { opacity: 0; }
        }
        .error-icon {
            font-size: 1.5em;
            font-weight: bold;
            color: red;
            animation: blink 1s infinite;
            cursor: help;
        }
        /* 休憩打刻追加セクション（デフォルト非表示） */
        .add-break-section {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .add-break-section:hover {
            background-color: #e8f0fe; /* ホバー時に少し色をつける */
        }

        /* 実際のフォーム部分（デフォルト非表示） */
        .break-form {
            display: none;
            padding-top: 10px;
        }

        /* アニメーション効果 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .show {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        .nav-button {
            font-size: 1em;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .nav-button:hover {
            background-color: #0056b3;
        }
        .header-container {
            display: flex;
            align-items: center; /* 高さを中央揃え */
            justify-content: flex-start; /* 左寄せ */
            gap: 20px; /* ボタンと見出しの間隔を調整 */
            margin-bottom: 20px;
        }

        h2 {
            margin-left: 20%; /* 30px だけ右に移動（適宜調整） */
        }
        h3 {
            margin-left: auto;
            font-size: 1.2em;
            color: #333;
        }
        h4 {
            text-align: center;
        }

        .warning-message {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            font-weight: bold;
        }

        .employee-info {
            display: flex;
            flex-direction: column; /* 縦並びにする */
            align-items: center; /* 中央揃え */
        }

        .event-info {
            font-size: 1em;
            color: #333;
            margin-top: 15px; /* 名前とイベントの間に余白を追加 */
        }

        /* セレクトボックスの外枠 */
        .select-container {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 350px; /* 必要に応じて調整 */
            text-align: center;
        }

        /* セレクトボックスのデザイン */
        .styled-select {
            text-align: center;
            text-align-last: center; /* 選択後も中央揃え */
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            font-size: 1em;
            appearance: none; /* デフォルトのUIを削除 */
            cursor: pointer;
        }

        /* ホバー時 */
        .styled-select:hover {
            border-color: #007bff;
        }

        /* フォーカス時 */
        .styled-select:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0px 0px 5px rgba(0, 91, 187, 0.5);
        }

        /* Chrome, Edge, Safariのオプション中央寄せ */
        .styled-select option {
            text-align: center;
        }


        @media screen and (max-width: 768px) {
            .header-container {
                flex-wrap: wrap; /* スマホでは折り返す */
                justify-content: center; /* ボタンが端に寄りすぎないように */
                gap: 10px;
            }

            .nav-button {
                min-width: auto;
                padding: 8px 12px;
                font-size: 0.9em;
            }

            .header-container h2 {
                font-size: 1.2em;
            }

            .header-container h3 {
                font-size: 1em;
            }
            table {
                display: block; /* テーブル全体をブロック表示 */
                overflow-x: auto; /* 横スクロールを有効に */
                white-space: nowrap; /* 折り返しを防ぐ */
            }
            
            th, td {
                min-width: 150px; /* セルの最小幅を設定して、スクロールをスムーズにする */
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const addBreakSection = document.querySelector(".add-break-section");
            const breakForm = document.querySelector(".break-form");

            addBreakSection.addEventListener("click", function () {
                breakForm.classList.add("show"); // 表示のみ（非表示にはしない）
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const eventSelect = document.getElementById("eventSelect");
            const hiddenEventInputs = document.querySelectorAll("input[name='event']"); // すべての `event` の hidden input を取得

            if (eventSelect && hiddenEventInputs.length > 0) {
                // 初期設定：すべての hidden input に選択された event_id を設定
                hiddenEventInputs.forEach(input => {
                    input.value = eventSelect.value || "{{ session('evId') }}";
                });

                // イベントが変更されたら、すべての hidden input も更新
                eventSelect.addEventListener("change", function() {
                    hiddenEventInputs.forEach(input => {
                        input.value = this.value;
                    });
                });
            } else {
                console.error("イベント選択セレクトボックスまたは hidden input が見つかりません");
            }
        });

    </script>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <button onclick="location.href='{{ route('attendance', ['company_id' => Auth::user()->company_id, 'employee_id' => Auth::user()->id]) }}'" class="nav-button">
                出勤簿へ遷移
            </button>
            <h2>{{ $year }}年 {{ $month }}月 {{ $day }}日</h2>
            <h3>{{ $name }}さん</h3>
        </div>

        <h1>打刻修正画面</h1>
        <!-- イベント選択のドロップダウン -->
        @if(!$disable)
        @if ($events->isNotEmpty())
            <div class="select-container">
                <select id="eventSelect" name="event_id" class="styled-select">
                    @if (empty($eventSelected) && !session('evId'))
                        <option value="" {{ session('evId') ? '' : 'selected' }}>イベントを選択してください</option>
                    @endif
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" 
                            @if (session('evId') == $event->id || (isset($eventSelected) && $eventSelected->id == $event->id)) 
                                selected 
                            @endif>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
            @if ($pending)
                <div class="warning-message">
                    ⚠ 未承認の打刻データがあります。<br>
                    未承認の打刻を削除するには、時間を **削除** してから「保存」を押してください。<br>
                    （Windows: `Backspace` / Mac: `Delete`）
                </div>
            @endif
            @if ($errors->any())
                <div class="warning-message">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        <table>
            <thead>
                <tr>
                    <th>項目</th>
                    <th>確定済みの打刻</th>
                    <th>未承認の打刻</th>
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
            $records['break_end']['previousRecord'] ?? [],
            $records['break_start']['currentRecord'] ?? [],
            $records['break_end']['currentRecord'] ?? []
        ))
        ->groupBy(function ($item) {
            return $item->before_adit_id ?? $item->id; // before_adit_id があればそれをキーにグループ化
        })
        ->map(function ($group) {
            // 最新のデータ（IDが一番大きいもの）
            $latest = $group->sortByDesc('id')->first();

            // 過去のデータ（before_adit_id が設定されているもの）
            $before = $group->where('id', $latest->before_adit_id)->first();

            return (object) [
                'id' => $latest->id,
                'adit_item' => $latest->adit_item,
                'minutes' => $latest->minutes, // 最新の minutes
                'status' => $latest->status, // 最新の status
                'before_minutes' => $before ? $before->minutes : null, // 過去の before_minutes（実際に before_adit_id に紐づくデータ）
                'before_status' => $before ? $before->status : null, // 過去の status
                'deleted' => $latest->deleted,
            ];
        })
        ->sortBy(fn($item) => \Carbon\Carbon::parse($item->minutes ?? $item->before_minutes))
        ->values();

    @endphp

    {{-- 出勤・退勤 --}}
    @foreach ($records as $aditItem => $record)
        @if (in_array($aditItem, ['work_start']))
            <tr @if (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                    class="error-row"
                @endif>
                <td>
                    @if (!empty($record['currentRecord']) && $record['currentRecord'][0]['status'] == 'pending' and $record['currentRecord'][0]['deleted'] == 1)
                        <span class="error-tooltip">
                            削除待ちの打刻です
                        </span>
                    @elseif (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                        <span class="error-tooltip">
                            未承認の打刻です
                        </span>
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
                    <div class="form-inline">
                        <form method="POST" action="{{ route('updateAttendance') }}">
                            @csrf
                            <input type="hidden" name="minutes" value="{{ !empty($record['currentRecord']) ? $record['currentRecord'][0]->minutes : ''}}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ $aditItem }}">
                            <input type="hidden" id="hiddenEventId" name="event" value="">
                            <input type="hidden" name="adit_id" value="{{ isset($record['previousRecord'][0]) ? $record['previousRecord'][0]->id : '' }}">

                            <input type="time" name="{{ $aditItem }}"
                                value="{{ !empty($record['currentRecord']) ? \Carbon\Carbon::parse($record['currentRecord'][0]->minutes)->format('H:i') : '' }}">
                            <button type="submit" class="save-btn">保存</button>
                        </form>
                        @if (!empty($record['previousRecord']) && empty($record['currentRecord']))
                        <form method="POST" action="{{ route('deleteAttendance') }}">
                            @csrf
                            <input type="hidden" name="minutes" value="{{ !empty($record['currentRecord']) ? $record['currentRecord'][0]->minutes : ''}}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ $aditItem }}">
                            <input type="hidden" id="hiddenEventId" name="event" value="">
                            <input type="hidden" name="adit_id" value="{{ isset($record['previousRecord'][0]) ? $record['previousRecord'][0]->id : '' }}">
                            <button type="submit" class="delete-btn">削除</button>
                        </form>
                        @else
                        <div style="width: 50px;"></div>
                        @endif
                    </div>
                </td>
            </tr>
        @endif
    @endforeach

    {{-- 休憩関連のデータ --}}
    @if ($combinedRecords->isNotEmpty())
        @foreach ($combinedRecords as $breakRecord)
            <tr @if ($breakRecord->status == 'pending')
                    class="error-row"
                @endif>
                <td>
                    @if ($breakRecord->status == 'pending' and $breakRecord->deleted == 1)
                        <span class="error-tooltip">
                            削除待ちの打刻です
                        </span>
                    @elseif ($breakRecord->status == 'pending' or $breakRecord->before_status == 'pending')
                        <span class="error-tooltip">
                            未承認の打刻です
                        </span>
                    @endif
                    {{ $breakRecord->adit_item === 'break_start' ? '休憩開始' : '休憩終了' }}
                </td>
                <td>
                    @if ($breakRecord->status == 'approved')
                        {{ $breakRecord->minutes ? \Carbon\Carbon::parse($breakRecord->minutes)->format('H:i') : '未登録' }}
                    @elseif ($breakRecord->before_status == 'approved')
                        {{ $breakRecord->before_minutes ? \Carbon\Carbon::parse($breakRecord->before_minutes)->format('H:i') : '未登録' }}
                    @else
                        {{'未登録'}}
                    @endif
                </td>
                <td>
                    <div class="form-inline">
                        <form method="POST" action="{{ route('updateAttendance') }}">
                            @csrf
                            <input type="hidden" name="minutes" value="{{ $breakRecord->status == 'pending' ? $breakRecord->minutes : ''}}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ $breakRecord->adit_item }}">
                            <input type="hidden" id="hiddenEventId" name="event" value="">
                            <input type="hidden" name="adit_id" value="{{ $breakRecord->id }}">
                            <input type="time" name="{{ $breakRecord->adit_item }}"
                                value="{{ $breakRecord->status == 'pending' ? \Carbon\Carbon::parse($breakRecord->minutes)->format('H:i') : '' }}">
                            <button type="submit" class="save-btn">保存</button>
                        </form>
                        @if ($breakRecord->status == 'approved' && $breakRecord->before_status == null)
                        <form method="POST" action="{{ route('deleteAttendance') }}">
                            @csrf
                            <input type="hidden" name="minutes" value="{{ $breakRecord->status == 'pending' ? $breakRecord->minutes : ''}}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ $breakRecord->adit_item }}">
                            <input type="hidden" id="hiddenEventId" name="event" value="">
                            <input type="hidden" name="adit_id" value="{{ $breakRecord->id }}">
                            <button type="submit" class="delete-btn">削除</button>
                        </form>
                        @else
                            <div style="width: 50px;"></div>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    @endif
    @foreach ($records as $aditItem => $record)
        @if (in_array($aditItem, ['work_end']))
            <tr @if (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                    class="error-row"
                @endif>
                <td>
                    @if (!empty($record['currentRecord']) && $record['currentRecord'][0]['status'] == 'pending' and $record['currentRecord'][0]['deleted'] == 1)
                        <span class="error-tooltip">
                            削除待ちの打刻です
                        </span>
                    @elseif (!empty($record['currentRecord']) && $record['currentRecord'][0]->status === 'pending')
                        <span class="error-tooltip">
                            未承認の打刻です
                        </span>
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
                    <div class="form-inline">
                        <form method="POST" action="{{ route('updateAttendance') }}">
                            @csrf
                            <input type="hidden" name="minutes" value="{{ !empty($record['currentRecord']) ? $record['currentRecord'][0]->minutes : '' }}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ $aditItem }}">
                            <input type="hidden" id="hiddenEventId" name="event" value="">
                            <input type="hidden" name="adit_id" value="{{ isset($record['previousRecord'][0]) ? $record['previousRecord'][0]->id : '' }}">
                            <input type="time" name="{{ $aditItem }}"
                                value="{{ !empty($record['currentRecord']) ? \Carbon\Carbon::parse($record['currentRecord'][0]->minutes)->format('H:i') : '' }}">
                            <button type="submit" class="save-btn">保存</button>
                        </form>
                        @if (!empty($record['previousRecord']) && empty($record['currentRecord']))
                        <form method="POST" action="{{ route('deleteAttendance') }}">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="minutes" value="{{ !empty($record['currentRecord']) ? $record['currentRecord'][0]->minutes : '' }}">
                            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                            <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                            <input type="hidden" name="adit_item" value="{{ $aditItem }}">
                            <input type="hidden" id="hiddenEventId" name="event" value="">
                            <input type="hidden" name="adit_id" value="{{ isset($record['previousRecord'][0]) ? $record['previousRecord'][0]->id : '' }}">
                            <button type="submit" class="delete-btn">削除</button>
                        </form>
                        @else
                            <div style="width: 50px;"></div>
                        @endif
                    </div>
                </td>
            </tr>
        @endif
    @endforeach
</tbody>


        </table>
        <div class="add-break-section">
            <h3>休憩打刻を追加</h2>
            <div class="break-form">
                <form method="POST" action="{{ route('updateAttendance') }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                    <input type="hidden" name="companyId" value="{{ Auth::User()->company_id }}">
                    <input type="hidden" id="hiddenEventId" name="event" value="">

                    <div class="form-group">
                        <label for="break_start">休憩開始時間:</label>
                        <input type="time" id="break_start" name="break_start">
                    </div>

                    <div class="form-group">
                        <label for="break_end">休憩終了時間:</label>
                        <input type="time" id="break_end" name="break_end">
                    </div>

                    <button type="submit" class="save-btn">追加する</button>
                </form>
            </div>
       
        </div>
        @elseif ($disable)
        <div style="text-align: center; margin: 20px 0; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">
            <strong>この日付の打刻データは修正できません。</strong>
        </div>
        @endif
    </div>
</body>
</html>
