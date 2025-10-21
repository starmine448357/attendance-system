@extends('layouts.user')

@section('title', '勤怠詳細')

@section('content')
<div class="attendance-detail-container">

    <h1 class="page-title">勤怠詳細</h1>

    {{-- ====== 承認待ち or 編集フォーム ====== --}}
    @if ($hasPendingRequest)
    {{-- ====== 閲覧のみモード ====== --}}
    <div class="detail-table">
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $attendance->user->name }}</div>
        </div>
        <div class="row">
            <div class="label">日付</div>
            <div class="value">{{ $attendance->date->format('Y年 n月 j日') }}</div>
        </div>
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value">{{ $attendance->start_time }} 〜 {{ $attendance->end_time }}</div>
        </div>
        @foreach ($attendance->rests as $i => $rest)
        <div class="row">
            <div class="label">休憩{{ $i+1 }}</div>
            <div class="value">{{ $rest->start }} 〜 {{ $rest->end }}</div>
        </div>
        @endforeach
        <div class="row">
            <div class="label">備考</div>
            <div class="value">{{ $attendance->note }}</div>
        </div>
    </div>

    <p class="pending-message">※承認待ちのため修正はできません。</p>

    @else
    {{-- ====== 編集フォームモード ====== --}}
    <form method="POST" action="{{ route('request.store') }}">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

        <div class="detail-table">

            <div class="row">
                <div class="label">名前</div>
                <div class="value">{{ $attendance->user->name }}</div>
            </div>

            <div class="row">
                <div class="label">日付</div>
                <div class="value">{{ $attendance->date->format('Y年 n月 j日') }}</div>
            </div>

            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value">
                    <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time) }}">
                    〜
                    <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time) }}">
                </div>
            </div>

            @foreach ($attendance->rests as $i => $rest)
            <div class="row">
                <div class="label">休憩{{ $i+1 }}</div>
                <div class="value">
                    <input type="time" name="rest_start[]" value="{{ old('rest_start.'.$i, $rest->start) }}">
                    〜
                    <input type="time" name="rest_end[]" value="{{ old('rest_end.'.$i, $rest->end) }}">
                </div>
            </div>
            @endforeach

            <div class="row">
                <div class="label">休憩追加</div>
                <div class="value">
                    <input type="time" name="rest_start[]" placeholder="--:--">
                    〜
                    <input type="time" name="rest_end[]" placeholder="--:--">
                </div>
            </div>

            <div class="row">
                <div class="label">備考</div>
                <div class="value">
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                </div>
            </div>

        </div>

        <button type="submit" class="submit-btn">修正申請</button>

    </form>
    @endif

</div>
@endsection