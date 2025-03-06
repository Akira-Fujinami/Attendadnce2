<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ一覧</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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

        .status {
            padding: 5px 10px;
            border-radius: 5px;
            color: #fff;
        }

        .status-working {
            background-color: #28a745;
        }

        .status-break {
            background-color: #ffc107;
        }

        .status-retired {
            background-color: #dc3545;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
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
        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        /* ヘッダーの全体コンテナ */
        .header-container {
            width: 100%;
            padding: 10px 0;
            display: flex;
            justify-content: center;
        }

        /* ナビゲーションメニュー */
        .top-links {
            display: grid;
            grid-template-columns: repeat(6, 1fr); /* 6分割 */
            gap: 10px;
            width: 90%;
            max-width: 1200px;
        }

        /* ボタンデザイン */
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 10px;
            font-size: 1em;
            font-weight: bold;
            border-radius: 8px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            white-space: nowrap; /* ボタン内の文字を折り返さない */
        }

        .btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        /* ログアウトボタン */
        .logout-form {
            width: 100%;
        }

        .logout-btn {
            width: 100%;
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        /* ⚠️ 未承認打刻ボタンの強調 */
        .alert-btn {
            background-color: #dc3545;
        }

        .alert-btn:hover {
            background-color: #c82333;
        }

        /* スマホ対応（画面幅が狭い場合） */
        @media (max-width: 768px) {
            .top-links {
                grid-template-columns: repeat(2, 1fr); /* 2列に変更 */
            }
        }
        .error-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            text-align: center;
        }

        .error-list {
            list-style-type: none;
            padding: 0;
            text-align: left;
        }

        .error-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            color: #dc3545;
            font-weight: bold;
        }

        .error-item a {
            color: #dc3545;
            text-decoration: none;
        }

        .error-item a:hover {
            text-decoration: underline;
            opacity: 0.8;
        }
        .no-errors {
            text-align: center;
            color: #28a745;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 10px;
        }
        .logout-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.65;
        }
        @media (max-width: 768px) {
            .top-links {
                flex-direction: column;
                align-items: center;
                margin-right: 10%;
            }

            .btn {
                width: 80%;
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
    <div class="container">
        <div class="header-container">
            <nav class="top-links">
                <form action="{{ route('logout') }}" method="POST" class="logout-form" id="logout-form">
                    @csrf
                    <button type="submit" class="btn logout-btn" id="logout-btn">
                        🚪 ログアウト
                    </button>
                </form>

                <a href="{{ route('staff') }}" class="btn">
                    ➕ スタッフ一覧
                </a>
                <a href="{{ route('attendanceList', ['companyId' => Auth::User()->id]) }}" class="btn">
                    📆 月次出勤簿
                </a>
                <a href="{{ route('showCalendar') }}" class="btn">
                    📅 カレンダー
                </a>
                <a href="{{ route('events.show') }}" class="btn">
                    🎉 イベント出勤簿
                </a>
                <a href="{{ route('appliedAdit', ['companyId' => Auth::User()->id]) }}" class="btn alert-btn">
                    ⚠️ 未承認打刻一覧
                </a>
            </nav>
        </div>
        <ul>
        </ul>
        <h1>エラーリスト</h1>

        <div class="error-container">
            @php
                $errorCount = 0;
            @endphp

            <ul class="error-list">
                @foreach ($EmployeeList as $employee)
                    @if (!empty($employee->errors))
                        @php $errorCount++; @endphp
                        @foreach ($employee->errors as $error)
                            <li class="error-item">
                                <a href="{{ route('attendanceDetails', ['date' => $error['date'], 'employeeId' => $error['employee_id'], 'companyId' => $error['company_id']]) }}">
                                    {{ $employee->name }}: {{ $error['name'] }}
                                </a>
                            </li>
                        @endforeach
                    @endif

                    @if (!empty($employee->pendingRecords))
                        @php $errorCount++; @endphp
                        @foreach ($employee->pendingRecords as $pendingRecord)
                            <li class="error-item">
                                <a href="{{ route('appliedAdit', ['companyId' => Auth::user()->id]) }}">
                                    {{ $employee->name }}: 未承認の打刻があります ({{ $pendingRecord['date'] }})
                                </a>
                            </li>
                        @endforeach
                    @endif
                @endforeach
            </ul>

            @if ($errorCount === 0)
                <p class="no-errors">エラーはありません。</p>
            @endif
        </div>

        <footer>© 2024 勤怠管理システム. All Rights Reserved.</footer>
    </div>
</body>
</html>
