<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'スタッフ画面')</title>

    {{-- ページ固有CSS（request.cssなど） --}}
    @yield('css')

    {{-- 共通CSS --}}
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
                    {{-- ✅ 状態に応じて表示内容を切り替え --}}
                    @if (isset($status) && $status === '退勤済')
                    <li><a href="{{ route('attendance.index') }}">今月の出勤一覧</a></li>
                    <li><a href="{{ route('request.index') }}">申請一覧</a></li>
                    @else
                    <li><a href="{{ route('attendance.record') }}">勤怠</a></li>
                    <li><a href="{{ route('attendance.index') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('request.index') }}">申請</a></li>
                    @endif

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

    {{-- ✅ これを追加 --}}
    @yield('js')

</body>

</html>