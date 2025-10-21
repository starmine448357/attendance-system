<header class="header">
    @include('partials.logo')

    <nav class="header__nav">
        <ul>
            <li><a href="/" class="header__logout">トップ</a></li>
            <li><a href="{{ route('login') }}" class="header__logout">ログイン</a></li>
        </ul>
    </nav>
</header>