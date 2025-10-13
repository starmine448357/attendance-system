<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'スタッフログイン')</title>
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
</head>

<body class="user-guest-body">
    <header class="user-header">
        <div class="user-header__inner">
            {{-- 共通ロゴ --}}
            @include('partials.logo')
        </div>
    </header>

    <main class="user-main">
        @yield('content')
    </main>
</body>

</html>