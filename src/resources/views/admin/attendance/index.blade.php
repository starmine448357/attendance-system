@extends('layouts.admin')

@section('title', $currentDate->format('Y年n月j日') . 'の勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-index.css') }}">
@endsection

@section('content')
@php
$weekMap = [
'Sun' => '日',
'Mon' => '月',
'Tue' => '火',
'Wed' => '水',
'Thu' => '木',
'Fri' => '金',
'Sat' => '土',
];

/**
* ✅ 分数 → HH:MM 形式へ変換するヘルパー
*/
function minutesToTimeFormat($minutes)
{
if ($minutes === null) return '';
$hours = floor($minutes / 60);
$mins = $minutes % 60;
return sprintf('%02d:%02d', $hours, $mins);
}
@endphp

<div class="attendance-index">

    {{-- ===============================
        タイトル部分（例：2025年10月30日の勤怠）
    =============================== --}}
    <h1 class="page-title">
        {{ $currentDate->format('Y年n月j日') }}の勤怠
    </h1>

    {{-- ===============================
        日付移動カード
    =============================== --}}
    <div class="day-card">
        {{-- 前日ボタン --}}
        <a href="{{ route('admin.attendance.index', ['date' => $currentDate->copy()->subDay()->format('Y-m-d')]) }}"
            class="day-card__btn day-card__btn--left">
            <img src="{{ asset('images/arrow-left.png') }}" class="arrow-icon" alt="prev">
            前日
        </a>

        {{-- 中央部（カレンダー表示） --}}
        <div class="day-card__center">
            <img src="{{ asset('images/calendar.png') }}" class="day-card__icon" alt="calendar">
            <span class="day-card__text current-day">
                {{ $currentDate->format('Y/m/d') }} ({{ $weekMap[$currentDate->format('D')] }})
            </span>
        </div>

        {{-- 翌日ボタン --}}
        <a href="{{ route('admin.attendance.index', ['date' => $currentDate->copy()->addDay()->format('Y-m-d')]) }}"
            class="day-card__btn day-card__btn--right">
            翌日
            <img src="{{ asset('images/arrow-left.png') }}" class="arrow-icon arrow-icon--right" alt="next">
        </a>
    </div>

    {{-- ===============================
        勤怠テーブル
    =============================== --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($attendances as $attendance)
            @php
            // 休憩合計（restsがある場合はその合計を優先）
            $totalRestMinutes = 0;
            if ($attendance->rests && $attendance->rests->count() > 0) {
            $totalRestMinutes = $attendance->rests->sum(function ($rest) {
            if ($rest->break_start && $rest->break_end) {
            $start = \Carbon\Carbon::parse($rest->break_start);
            $end = \Carbon\Carbon::parse($rest->break_end);
            return abs($end->diffInMinutes($start, false));
            }
            return 0;
            });
            } elseif ($attendance->break_duration) {
            $totalRestMinutes = $attendance->break_duration;
            }

            // 合計（出勤〜退勤 − 休憩）
            $workMinutes = null;
            if ($attendance->start_time && $attendance->end_time) {
            $start = \Carbon\Carbon::parse($attendance->start_time);
            $end = \Carbon\Carbon::parse($attendance->end_time);
            $workMinutes = abs($end->diffInMinutes($start, false)) - $totalRestMinutes;
            if ($workMinutes < 0) $workMinutes=0;
                }
                @endphp

                <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->start_time?->format('H:i') }}</td>
                <td>{{ $attendance->end_time?->format('H:i') }}</td>
                <td>{{ minutesToTimeFormat($totalRestMinutes) }}</td>
                <td>{{ minutesToTimeFormat($workMinutes ?? $attendance->total_duration) }}</td>
                <td>
                    <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">
                        詳細
                    </a>
                </td>
                </tr>
                @endforeach
        </tbody>
    </table>

</div>
@endsection