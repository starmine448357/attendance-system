@extends('layouts.user_guest')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<form method="POST" action="{{ route('register') }}" class="authenticate register-form">
    @csrf

    <h1 class="page__title">会員登録</h1>

    {{-- 名前 --}}
    <label for="name" class="entry__name">名前</label>
    <input id="name" type="text" name="name" class="input" value="{{ old('name') }}" required autofocus>
    <div class="form__error">@error('name') {{ $message }} @enderror</div>

    {{-- メールアドレス --}}
    <label for="email" class="entry__name">メールアドレス</label>
    <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required>
    <div class="form__error">@error('email') {{ $message }} @enderror</div>

    {{-- パスワード --}}
    <label for="password" class="entry__name">パスワード</label>
    <input id="password" type="password" name="password" class="input" required>
    <div class="form__error">@error('password') {{ $message }} @enderror</div>

    {{-- パスワード確認 --}}
    <label for="password_confirmation" class="entry__name">パスワード確認</label>
    <input id="password_confirmation" type="password" name="password_confirmation" class="input" required>
    <div class="form__error">@error('password_confirmation') {{ $message }} @enderror</div>

    {{-- 登録ボタン --}}
    <button type="submit" class="btn btn--big">登録する</button>

    {{-- ログインリンク --}}
    <a href="{{ route('login') }}" class="link">ログインはこちら</a>
</form>
@endsection