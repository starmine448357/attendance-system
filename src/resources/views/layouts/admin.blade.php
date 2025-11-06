<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理者画面')</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    {{-- ✅ ここを追加 --}}
    @yield('css')
</head>

<body>
    <header class="admin-header">
        <div class="admin-header__inner">
            @include('partials.logo')

            <nav class="admin-header__nav">
                <ul>
                    <li><a href="{{ route('admin.attendance.index') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('admin.staff.index') }}">スタッフ一覧</a></li>
                    <li><a href="{{ route('admin.request.index') }}">申請一覧</a></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="logout-btn">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-main">
        @yield('content')
    </main>
</body>

</html>