@extends('layouts.admin_guest')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-auth.css') }}">
@endsection

@section('content')
<form action="{{ route('admin.login.submit') }}" method="POST" class="authenticate center">
    @csrf
    <h1 class="page__title">管理者ログイン</h1>

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
</form>
@endsection