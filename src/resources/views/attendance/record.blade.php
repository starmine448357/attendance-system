@extends('layouts.user')

@section('title', '勤怠打刻')

@section('content')
<div class="record-container">
    {{-- 勤務状態 --}}
    <div class="record-status">
        @if (!$attendance)
        <span class="status gray">勤務外</span>
        @elseif ($attendance && !$attendance->end_time)
        @if (!$attendance->break_start)
        <span class="status black">出勤中</span>
        @elseif ($attendance->break_start && !$attendance->break_end)
        <span class="status gray">休憩中</span>
        @endif
        @else
        <span class="status gray">退勤済</span>
        @endif
    </div>

    {{-- 日付と時刻 --}}
    <div class="record-date-time">
        <p class="date">{{ now()->format('Y年n月j日(D)') }}</p>
        <p class="time">{{ now()->format('H:i') }}</p>
    </div>

    {{-- ボタン --}}
    <div class="record-buttons">
        @if (!$attendance)
        {{-- 出勤前 --}}
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="type" value="start">
            <button type="submit" class="btn black">出勤</button>
        </form>
        @elseif ($attendance && !$attendance->end_time)
        @if (!$attendance->break_start)
        {{-- 出勤中 --}}
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="type" value="end">
            <button type="submit" class="btn black">退勤</button>
        </form>

        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="type" value="break_start">
            <button type="submit" class="btn white">休憩入</button>
        </form>
        @elseif ($attendance->break_start && !$attendance->break_end)
        {{-- 休憩中 --}}
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="type" value="break_end">
            <button type="submit" class="btn white">休憩戻</button>
        </form>
        @endif
        @else
        {{-- 退勤済 --}}
        <p class="finish-text">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection