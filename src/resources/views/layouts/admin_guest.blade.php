<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理者ログイン')</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body class="admin-guest-body">
    <header class="admin-header">
        <div class="admin-header__inner">
            @include('partials.logo')
        </div>
    </header>

    <main class="admin-main">
        @yield('content')
    </main>
</body>

</html>