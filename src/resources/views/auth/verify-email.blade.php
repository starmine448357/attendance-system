@extends('layouts.user_guest')

@section('title', 'メール認証')

@section('content')
<div class="verify-container">
    <h1 class="verify-title">メール認証はお済みですか？</h1>

    {{-- 再送信メッセージ --}}
    @if (session('status') == 'verification-link-sent')
    <p class="verify-success">新しい認証メールを送信しました！</p>
    @endif

    <p class="verify-text">
        登録されたメールアドレスに認証用リンクを送信しました。<br>
        メール内のリンクをクリックして認証を完了してください。<br>
        もしメールを受け取っていない場合は、以下から再送できます。
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="verify-form">
        @csrf
        <button type="submit" class="verify-btn">認証メールを再送信</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="logout-form">
        @csrf
        <button type="submit" class="logout-btn">ログアウト</button>
    </form>
</div>
@endsection