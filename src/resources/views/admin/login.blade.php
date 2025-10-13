@extends('layouts.user_guest')

@section('title', 'スタッフログイン')

@section('content')
<div class="login-container">
    <h1 class="login-title">ログイン</h1>

    {{-- エラーメッセージ --}}
    @if ($errors->any())
    <div class="error-message">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" required>
        </div>

        <button type="submit" class="login-btn">ログイン</button>
    </form>

    <div class="register-link">
        <p>アカウントをお持ちでない方は <a href="{{ route('register') }}">こちら</a></p>
    </div>
</div>
@endsection