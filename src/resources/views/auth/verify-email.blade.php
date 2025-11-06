@extends('layouts.user_guest')

@section('title', 'メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="verify-container text-center">
    <p>
        ご登録いただいたメールアドレスに認証メールを送信しました。<br>
        メールを確認し、認証を完了してください。
    </p>

    {{-- ✅ 認証済みなら遷移、未認証なら押せない見た目だけ --}}
    @if (auth()->check() && auth()->user()->hasVerifiedEmail())
    {{-- 認証済み → 勤怠画面へ遷移 --}}
    <a href="{{ route('attendance.record') }}"
        class="btn btn--secondary btn--full"
        style="margin:20px 0;">
        認証はこちらから
    </a>
    @else
    {{-- 未認証 → hrefなし＋pointer-events:none --}}
    <a href="http://localhost:8025/" class="btn btn--secondary btn--full" style="margin:20px 0;">
        認証はこちらから
    </a>
    @endif

    {{-- 認証メール再送 --}}
    <form method="POST" action="{{ route('verification.resend.guest') }}">
        @csrf
        <button type="submit" class="link-btn">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection