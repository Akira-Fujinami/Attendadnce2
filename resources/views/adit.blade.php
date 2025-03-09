<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出勤管理システム</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 50px;
        }

        h1 {
            color: #333;
        }

        .time-display {
            font-size: 2em;
            color: #333;
            margin-top: 20px;
        }

        .button-group {
            margin-top: 30px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1.2em;
            margin: 10px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .button-green {
            background-color: #28a745;
        }

        .button-yellow {
            background-color: #ffc107;
        }

        .button-blue {
            background-color: #17a2b8;
        }

        .button-red {
            background-color: #dc3545;
        }

        .button:hover {
            opacity: 0.9;
        }

        .navigation {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .navigation .button {
            font-size: 1em;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
        }

        .navigation .button:hover {
            opacity: 0.9;
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

        .button[disabled] {
            background-color: #6c757d; /* グレー色 */
            color: #fff; /* テキスト色 */
            cursor: not-allowed; /* ポインターを無効化表示 */
            opacity: 0.65; /* 半透明 */
        }
        .error-link {
            color: #dc3545; /* 赤色 */
            text-decoration: none;
            font-weight: bold;
        }

        .error-link:hover {
            text-decoration: underline;
            opacity: 0.8; /* マウスホバー時に少し透明感を追加 */
        }

        .logout-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .logout-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.65;
        }
        @media screen and (max-width: 768px) {
            .navigation, .logout {
                position: static; /* 絶対配置を解除 */
                text-align: center;
                margin-bottom: 10px;
            }

            .navigation .button, .logout .button {
                width: 90%; /* 画面幅いっぱいに調整 */
                display: block;
                margin: 5px auto; /* 上下に間隔を確保 */
            }

            .button-group {
                display: flex;
                flex-wrap: wrap; /* 必要に応じて折り返し */
                justify-content: center; /* ボタンを中央揃え */
                gap: 10px;
            }

            .button {
                width: 45%; /* 画面幅の約半分 */
                padding: 12px;
                font-size: 1em;
                text-align: center;
            }

            .button[disabled] {
                opacity: 0.65;
            }
        }


    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutForm = document.getElementById("logout-form");
            const logoutButton = document.getElementById("logout-btn");

            logoutForm.addEventListener("submit", function(event) {
                logoutButton.disabled = true; // ボタンを無効化
                logoutButton.textContent = "処理中..."; // ログイン中のメッセージに変更
            });
        });
    </script>
</head>
<body>
    <!-- 出勤簿へ遷移ボタン -->
    <div class="navigation">
        <a href="{{ route('attendance', ['company_id' => Auth::user()->company_id, 'employee_id' => Auth::user()->id]) }}" class="button">出勤簿へ遷移</a>
    </div>

    <!-- ログアウトボタン -->
    @if(empty($data['event']))
    <div class="logout">
        <form action="{{ route('logout') }}" method="POST" id="logout-form">
            @csrf
            <button type="submit" class="button" id="logout-btn">ログアウト</button>
        </form>
    </div>
    @else
    <div class="logout">
        <form action="{{ route('qr_logout') }}" method="POST" id="logout-form">
            @csrf
            <input type="hidden" name="event_id" value="{{ $data['event']->id }}">
            <button type="submit" class="button" id="logout-btn">ログアウト</button>
        </form>
    </div>
    @endif

    <div class="container">
        @if($data['latestAdit'])
            <div class="logout-message">
                ⚠ 打刻が完了したら、ログアウトして安全に終了してください。
            </div>
        @endif
        <h1>{{ Auth::user()->name }} さん  @if (session('event')) @ {{ session('event') }} @endif</h1>
        <div class="time-display" id="current-time">
            <!-- 時間がここに表示されます -->
        </div>
        <div style="margin-top: 20px; font-size: 1.5em;
            @if($data['latestAdit'] == 'work_start') color: #28a745; 
               @elseif($data['latestAdit'] == 'break_start') color: #ffc107
               @elseif($data['latestAdit'] == 'break_end') color: #28a745
               @elseif($data['latestAdit'] == 'work_end') color: #dc3545 
               @endif">
                {{$data['status']}}
        </div>

        <div class="button-group">
    <form action="{{ route('adit') }}" method="POST">
        @csrf
        <button type="submit" name="adit_item" value="work_start" class="button button-green" @if($data['aditExists'] or $data['latestAdit'] == 'work_end') disabled @endif>出勤</button>
        <button type="submit" name="adit_item" value="break_start" class="button button-yellow" @if(!$data['aditExists'] or $data['latestAdit'] == 'work_end' or $data['latestAdit'] == 'break_start') disabled @endif>休憩開始</button>
        <button type="submit" name="adit_item" value="break_end" class="button button-blue" @if(!$data['aditExists'] or $data['latestAdit'] == 'work_end' or $data['latestAdit'] == 'work_start' or $data['latestAdit'] == 'break_end') disabled @endif>休憩終了</button>
        <button type="submit" name="adit_item" value="work_end" class="button button-red" @if(!$data['aditExists'] or $data['latestAdit'] == 'work_end' or $data['latestAdit'] == 'break_start') disabled @endif>退勤</button>
        <input type="hidden" name="employee_id" value="{{ Auth::user()->id }}">
        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
        <input type="hidden" name="wage" value="{{ Auth::user()->hourly_wage }}">
        <input type="hidden" name="transportation" value="{{ Auth::user()->transportation_fee }}">
        @if(!empty($data['event']))
            <input type="hidden" name="event" value="{{ $data['event']->id }}">
        @endif
    </form>
    @if(!empty($data['errors']))
        @foreach($data['errors'] as $error)
            <li>
                <a href="{{ route('editAttendance', ['date' => $error['date'], 'employeeId' => Auth::User()->id]) }}" class="error-link">
                    {{ $error['date'] }}: 
                    <span class="error-message">打刻が不正です</span>
                </a>
            </li>
        @endforeach
    @endif
    @if (!empty($data['pending']))
        @foreach($data['pending'] as $pending)
            <li>
                <a href="{{ route('editAttendance', ['date' => $pending['date'], 'employeeId' => Auth::User()->id]) }}" class="error-link">
                    {{ $pending['date'] }}: 
                    <span class="error-message">未承認の打刻があります</span>
                </a>
            </li>
        @endforeach
    @endif
    @if (!empty($data['rejected']))
        @foreach($data['rejected'] as $rejected)
            <li>
                <a href="#" class="error-link rejected-record" 
                    data-date="{{ $rejected['date'] }}" 
                    data-employee-id="{{ Auth::User()->id }}"
                    data-url-edit="{{ route('editAttendance', ['date' => $rejected['date'], 'employeeId' => Auth::User()->id]) }}"
                    data-url-confirm="{{ route('confirmAdit', ['date' => $rejected['date'], 'employeeId' => Auth::User()->id]) }}">
                    
                    {{ $rejected['date'] }}:
                    <span class="error-message">
                        @foreach ($rejected['records'] as $record)
                            {{ $record['time'] }}（{{ $record['type'] }}）
                        @endforeach
                        の打刻が却下されました
                    </span>
                </a>
            </li>
            <div id="confirmDialog" class="modal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">
                <p>この打刻の処理を選択してください</p>
                <button id="editButton" class="button button-blue">編集する</button>
                <button id="confirmButton" class="button button-green">確認済みにする</button>
                <button id="closeDialog" class="button button-red">キャンセル</button>
            </div>
        @endforeach
    @endif




</div>

    </div>

    <script>
        // 時間を動的に更新する関数
        function updateTime() {
            const now = new Date(); // 現在の日時を取得
            const hours = String(now.getHours()).padStart(2, '0'); // 時間を2桁に整形
            const minutes = String(now.getMinutes()).padStart(2, '0'); // 分を2桁に整形
            const seconds = String(now.getSeconds()).padStart(2, '0'); // 秒を2桁に整形
            const formattedTime = `${hours}:${minutes}:${seconds}`; // "HH:mm:ss"形式で表示

            document.getElementById('current-time').textContent = formattedTime; // HTMLに表示
        }

        // 初期表示
        updateTime();

        // 毎秒時間を更新
        setInterval(updateTime, 1000);

        document.addEventListener("DOMContentLoaded", function() {
            const rejectedRecords = document.querySelectorAll('.rejected-record');
            const confirmDialog = document.getElementById('confirmDialog');
            const editButton = document.getElementById('editButton');
            const confirmButton = document.getElementById('confirmButton');
            const closeDialog = document.getElementById('closeDialog');

            let selectedUrlEdit = "";
            let selectedUrlConfirm = "";
            let selectedRecord = null;

            rejectedRecords.forEach(record => {
                record.addEventListener("click", function(event) {
                    event.preventDefault();
                    selectedUrlEdit = this.getAttribute("data-url-edit");
                    selectedUrlConfirm = this.getAttribute("data-url-confirm");
                    selectedRecord = this; // クリックした要素を保存
                    confirmDialog.style.display = "block";
                });
            });

            // 「編集する」ボタンがクリックされた場合
            editButton.addEventListener("click", function() {
                window.location.href = selectedUrlEdit;
            });

            // 「確認済みにする」ボタンがクリックされた場合
            confirmButton.addEventListener("click", function() {
                let formData = new FormData();
                formData.append("date", selectedRecord.getAttribute("data-date"));
                formData.append("employeeId", selectedRecord.getAttribute("data-employee-id"));
                formData.append("_token", document.querySelector('meta[name="csrf-token"]').getAttribute("content")); // CSRFトークンを追加
                console.log(formData);

                fetch(selectedUrlConfirm, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        date: selectedRecord.getAttribute("data-date"),
                        employeeId: selectedRecord.getAttribute("data-employee-id")
                    })
                })

                .then(response => response.json()) 
                .then(data => {
                    if (data.success) {
                        alert("確認済みにしました！");
                        window.location.reload();
                    } else {
                        alert("エラー: " + (data.message || "不明なエラー"));
                    }
                })
                .catch(error => {
                    console.error("エラー:", error);
                    alert("サーバーエラーが発生しました。\n" + error.message);
                });

                confirmDialog.style.display = "none";
            });

            // 「キャンセル」ボタンがクリックされた場合
            closeDialog.addEventListener("click", function() {
                confirmDialog.style.display = "none";
            });
        });
    </script>
</body>
</html>
