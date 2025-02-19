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


        /* ヘッダー（ログアウト & トップへボタンを左右配置） */
        .header-container {
            width: 100%;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ボタン共通デザイン */
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

        /* トップボタン */
        .top-btn {
            background-color: #28a745;
        }

        .top-btn:hover {
            background-color: #218838;
        }

        /* ログアウトボタン */
        .logout-btn {
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        /* ナビゲーションメニュー */
        .top-links {
            display: flex;
            gap: 10px;
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* スマホ対応 */
        @media (max-width: 768px) {
            .top-links {
                flex-direction: column;
                align-items: center;
            }

            .header-container {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
        }

        /* フィルターフォーム（ステータス選択）の中央寄せ */
        .filter-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px; /* ラベルとセレクトボックスの間隔 */
            margin: 20px 0; /* 上下の余白を追加 */
            text-align: center;
        }

        /* ラベルのデザイン */
        .filter-form label {
            font-size: 1em;
            color: #333;
            font-weight: bold;
        }

        /* セレクトボックスのデザイン */
        .filter-form select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .logout-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.65;
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
        <!-- ヘッダーエリア -->
        <div class="header-container">
            <!-- トップページボタン（左上） -->
            <a href="{{ route('top') }}" class="btn top-btn">
                🏠 トップへ
            </a>

            <!-- ログアウトボタン（右上） -->
            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="btn logout-btn" id="logout-btn">
                    🚪 ログアウト
                </button>
            </form>
        </div>

        <!-- ナビゲーションメニュー -->
        <nav class="top-links">
            <a href="{{ route('staffCreate') }}" class="btn">
                ➕ スタッフ追加
            </a>
        </nav>

        <div class="header">
            <h1>スタッフ一覧</h1>
            <form method="GET" action="{{ route('staff') }}" class="filter-form">
                <label for="status">ステータス:</label>
                <select id="status" name="status" onchange="this.form.submit()">
                    <option value="すべて" {{ $currentStatus === 'すべて' ? 'selected' : '' }}>すべて</option>
                    <option value="在職中" {{ $currentStatus === '在職中' ? 'selected' : '' }}>在職中</option>
                    <option value="休職中" {{ $currentStatus === '休職中' ? 'selected' : '' }}>休職中</option>
                    <option value="退職済み" {{ $currentStatus === '退職済み' ? 'selected' : '' }}>退職済み</option>
                </select>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メール</th>
                    <th>勤務状況</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($EmployeeList as $employee)
                <tr>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>
                        <span class="status 
                            @if ($employee->retired === '在職中') status-working
                            @elseif ($employee->retired === '休職中') status-break
                            @elseif ($employee->retired === '退職済み') status-retired
                            @endif
                        ">
                            {{ $employee->retired }}
                        </span>
                    </td>
                    <td><a href="{{ route('staffDetail', ['employeeId' => $employee->id, 'companyId' => $employee->company_id]) }}" class="btn">詳細を見る</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <footer>© 2024 勤怠管理システム. All Rights Reserved.</footer>
    </div>
</body>
</html>
