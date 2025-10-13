<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'スタッフ画面')</title>
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
</head>

<body class="user-body">
    <header class="user-header">
        <div class="user-header__inner">
            {{-- 共通ロゴ --}}
            @include('partials.logo')

            {{-- ナビゲーション --}}
            <nav class="user-header__nav">
                <ul>
                    <li><a href="{{ route('attendance.index') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('request.index') }}">申請</a></li>
                    <li><a href="{{ route('attendance.record') }}">打刻</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-btn">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="user-main">
        @yield('content')
    </main>
</body>

</html>