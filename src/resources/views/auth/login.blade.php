@extends('layouts.user_guest')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<form action="{{ route('login') }}" method="POST" class="authenticate center">
    @csrf
    <h1 class="page__title">ログイン</h1>

    {{-- メールアドレス --}}
    <label for="email" class="entry__name">メールアドレス</label>
    <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required autofocus>
    <div class="form__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>

    {{-- パスワード --}}
    <label for="password" class="entry__name">パスワード</label>
    <input id="password" type="password" name="password" class="input" required>
    <div class="form__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>

    {{-- ボタン --}}
    <button type="submit" class="btn btn--big">ログインする</button>

    {{-- リンク --}}
    <a href="{{ route('register') }}" class="link">会員登録はこちら</a>
</form>
@endsection