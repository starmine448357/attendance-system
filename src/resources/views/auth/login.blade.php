@extends('layouts.user_guest')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<form method="POST" action="{{ route('login') }}" class="authenticate login-form">
    @csrf

    {{-- ===============================
         ページタイトル
    =============================== --}}
    <h1 class="page__title">ログイン</h1>

    {{-- ===============================
         メールアドレス入力欄
         ※ type="email"を外してLaravel側のみでバリデーション
    =============================== --}}
    <label for="email" class="entry__name">メールアドレス</label>
    <input id="email" name="email" class="input" value="{{ old('email') }}" autofocus>
    <div class="form__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>

    {{-- ===============================
         パスワード入力欄
         ※ ブラウザエラー防止のため required 不使用
    =============================== --}}
    <label for="password" class="entry__name">パスワード</label>
    <input id="password" type="password" name="password" class="input">
    <div class="form__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>

    {{-- ===============================
         ログインボタン
    =============================== --}}
    <button type="submit" class="btn btn--big">ログインする</button>

    {{-- ===============================
         新規登録リンク
    =============================== --}}
    <a href="{{ route('register') }}" class="link">会員登録はこちら</a>
</form>
@endsection