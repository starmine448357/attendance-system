@extends('layouts.user')

@section('title', '勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-record.css') }}">
@endsection

@section('content')
<div class="record-container">

    {{-- ===============================
         ステータス表示
    =============================== --}}
    <div class="record-status">
        @if (!$attendance)
        <span class="status gray">勤務外</span>
        @elseif ($attendance->status === '出勤中')
        <span class="status black">出勤中</span>
        @elseif ($attendance->status === '休憩中')
        <span class="status lightgray">休憩中</span>
        @elseif ($attendance->status === '退勤済')
        <span class="status gray">退勤済</span>
        @else
        <span class="status gray">勤務外</span>
        @endif
    </div>

    {{-- ===============================
         日付と時刻
    =============================== --}}
    <div class="record-date-time">
        @php
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[now()->dayOfWeek];
        @endphp
        <p class="date">{{ now()->format('Y年n月j日') }}（{{ $weekday }}）</p>
        <p class="time">{{ now()->format('H:i') }}</p>
    </div>

    {{-- ===============================
         打刻ボタン
    =============================== --}}
    <div class="record-buttons">
        @if (!$attendance)
        {{-- 出勤前 --}}
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="type" value="start">
            <button type="submit" class="btn black">出勤</button>
        </form>

        @elseif ($attendance->status === '出勤中')
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

        @elseif ($attendance->status === '休憩中')
        {{-- 休憩中 --}}
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="type" value="break_end">
            <button type="submit" class="btn white">休憩戻</button>
        </form>

        @elseif ($attendance->status === '退勤済')
        {{-- 退勤済 --}}
        <p class="finish-text">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection