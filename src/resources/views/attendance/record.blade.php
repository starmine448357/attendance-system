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
         日付と時刻（リアルタイム更新）
    =============================== --}}
    <p id="current-date" class="date">
        {{ now()->format('Y年 n月 j日（D）') }}
    </p>
    <p id="current-time" class="time">
        {{ now()->format('H:i') }}
    </p>

    {{-- ===============================
         打刻ボタン
    =============================== --}}
    <div class="record-buttons">
        {{-- 出勤前 --}}
        @if (!$attendance)
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <button type="submit" name="type" value="start" class="btn black">出勤</button>
        </form>

        {{-- 出勤中 --}}
        @elseif ($attendance->status === '出勤中')
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <button type="submit" name="type" value="end" class="btn black">退勤</button>
        </form>

        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <button type="submit" name="type" value="break_start" class="btn white">休憩入</button>
        </form>

        {{-- 休憩中 --}}
        @elseif ($attendance->status === '休憩中')
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <button type="submit" name="type" value="break_end" class="btn white">休憩戻</button>
        </form>

        {{-- 退勤済 --}}
        @elseif ($attendance->status === '退勤済')
        <p class="finish-text">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection

{{-- ===============================
     リアルタイム時計スクリプト
=============================== --}}
@section('js')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const dateEl = document.getElementById("current-date");
        const timeEl = document.getElementById("current-time");
        const weekdays = ["日", "月", "火", "水", "木", "金", "土"];

        function updateDateTime() {
            const now = new Date();

            // 日付
            const year = now.getFullYear();
            const month = now.getMonth() + 1;
            const day = now.getDate();
            const weekday = weekdays[now.getDay()];
            dateEl.textContent = `${year}年 ${month}月 ${day}日（${weekday}）`;

            // 時刻（秒なし）
            const hours = String(now.getHours()).padStart(2, "0");
            const minutes = String(now.getMinutes()).padStart(2, "0");
            timeEl.textContent = `${hours}:${minutes}`;
        }

        // 初回表示
        updateDateTime();

        // 1秒ごとにチェックして、分が変わったときだけ更新
        let lastMinute = new Date().getMinutes();
        setInterval(() => {
            const now = new Date();
            if (now.getMinutes() !== lastMinute) {
                lastMinute = now.getMinutes();
                updateDateTime();
            }
        }, 1000);
    });
</script>
@endsection