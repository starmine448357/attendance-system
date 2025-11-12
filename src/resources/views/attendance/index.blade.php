@extends('layouts.user')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
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
'Sat' => '土'
];

/**
* ✅ 分数 → HH:MM 形式へ変換するヘルパー
* （function_exists で重複定義を防止）
*/
if (!function_exists('minutesToTimeFormat')) {
function minutesToTimeFormat($minutes)
{
if ($minutes === null) return '';
$hours = floor($minutes / 60);
$mins = $minutes % 60;
return sprintf('%02d:%02d', $hours, $mins);
}
}
@endphp

<div class="attendance-index">
    <h1 class="page-title">勤怠一覧</h1>

    {{-- ===============================
         月移動カード
    =============================== --}}
    <div class="month-card">
        <a href="{{ route('attendance.index', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}"
            class="month-card__btn month-card__btn--left">
            <img src="{{ asset('images/arrow-left.png') }}" class="arrow-icon" alt="prev">
            前月
        </a>

        <div class="month-card__center">
            <img src="{{ asset('images/calendar.png') }}" class="month-card__icon" alt="calendar">
            <span class="month-card__text current-month">{{ $currentMonth->format('Y年m月') }}</span>
        </div>

        <a href="{{ route('attendance.index', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}"
            class="month-card__btn month-card__btn--right">
            翌月
            <img src="{{ asset('images/arrow-left.png') }}" class="arrow-icon arrow-icon--right" alt="next">
        </a>
    </div>

    {{-- ===============================
         勤怠テーブル
    =============================== --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($days as $day)
            @php
            $key = $day->format('Y-m-d');
            $record = $attendances[$key] ?? null;
            @endphp
            <tr>
                <td>{{ $day->format('m/d') }} ({{ $weekMap[$day->format('D')] }})</td>
                <td>{{ $record?->start_time?->format('H:i') }}</td>
                <td>{{ $record?->end_time?->format('H:i') }}</td>

                {{-- 休憩時間 --}}
                <td>
                    @if ($record && $record->rests->count() > 0)
                    @php
                    $totalRestMinutes = $record->rests->sum(function ($rest) {
                    if ($rest->break_start && $rest->break_end) {
                    $start = \Carbon\Carbon::parse($rest->break_start);
                    $end = \Carbon\Carbon::parse($rest->break_end);
                    // 差が負にならないよう絶対値で計算
                    return abs($end->diffInMinutes($start, false));
                    }
                    return 0;
                    });
                    @endphp
                    {{ minutesToTimeFormat($totalRestMinutes) }}
                    @else
                    {{ minutesToTimeFormat($record?->break_duration) }}
                    @endif
                </td>

                {{-- 合計（勤務時間 = 出勤〜退勤 − 休憩） --}}
                <td>
                    @if ($record && $record->start_time && $record->end_time)
                    @php
                    $start = \Carbon\Carbon::parse($record->start_time);
                    $end = \Carbon\Carbon::parse($record->end_time);
                    $restMinutes = $totalRestMinutes ?? ($record->break_duration ?? 0);
                    $workMinutes = abs($end->diffInMinutes($start, false)) - $restMinutes;
                    @endphp
                    {{ minutesToTimeFormat(max($workMinutes, 0)) }}
                    @else
                    {{ minutesToTimeFormat($record?->total_duration) }}
                    @endif
                </td>

                <td>
                    <a href="{{ $record ? route('attendance.detail', ['id' => $record->id]) : route('attendance.detail', ['id' => $key]) }}"
                        class="detail-link">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection