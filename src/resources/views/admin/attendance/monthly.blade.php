@extends('layouts.admin')

@section('title', '月次勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-monthly.css') }}">
@endsection

@section('content')
@php
use Carbon\Carbon;

$weekMap = ['Sun'=>'日','Mon'=>'月','Tue'=>'火','Wed'=>'水','Thu'=>'木','Fri'=>'金','Sat'=>'土'];

/** 分数→HH:MM変換 */
if (!function_exists('minutesToTimeFormat')) {
function minutesToTimeFormat($minutes) {
if ($minutes === null) return '';
$hours = floor($minutes / 60);
$mins = $minutes % 60;
return sprintf('%02d:%02d', $hours, $mins);
}
}
@endphp

<div class="attendance-index">
    <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

    {{-- ===============================
         月移動カード
    =============================== --}}
    <div class="month-card">
        <a href="{{ route('admin.attendance.monthly', [
            'user_id' => $user->id,
            'month' => $currentMonth->copy()->subMonth()->format('Y-m')
        ]) }}" class="month-card__btn month-card__btn--left">
            <img src="{{ asset('images/arrow-left.png') }}" class="arrow-icon" alt="prev">
            前月
        </a>

        <div class="month-card__center">
            <img src="{{ asset('images/calendar.png') }}" class="month-card__icon" alt="calendar">
            <span class="month-card__text current-month">{{ $currentMonth->format('Y/m') }}</span>
        </div>

        <a href="{{ route('admin.attendance.monthly', [
            'user_id' => $user->id,
            'month' => $currentMonth->copy()->addMonth()->format('Y-m')
        ]) }}" class="month-card__btn month-card__btn--right">
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

            // ✅ 休憩合計（マイナス防止）
            $totalRestMinutes = 0;
            if ($record && $record->rests && $record->rests->count() > 0) {
            $totalRestMinutes = $record->rests->sum(function ($rest) {
            if ($rest->break_start && $rest->break_end) {
            $start = \Carbon\Carbon::parse($rest->break_start);
            $end = \Carbon\Carbon::parse($rest->break_end);
            return abs($end->diffInMinutes($start, false));
            }
            return 0;
            });
            } elseif ($record?->break_duration) {
            $totalRestMinutes = $record->break_duration;
            }

            // ✅ 労働合計（出勤～退勤 － 休憩）
            $workMinutes = null;
            if ($record?->start_time && $record?->end_time) {
            $start = \Carbon\Carbon::parse($record->start_time);
            $end = \Carbon\Carbon::parse($record->end_time);
            $workMinutes = abs($end->diffInMinutes($start, false)) - $totalRestMinutes;
            if ($workMinutes < 0) $workMinutes=0; // マイナス防止
                }
                @endphp

                <tr>
                <td>{{ $day->format('m/d') }} ({{ $weekMap[$day->format('D')] }})</td>
                <td>{{ $record?->start_time ? Carbon::parse($record->start_time)->format('H:i') : '' }}</td>
                <td>{{ $record?->end_time ? Carbon::parse($record->end_time)->format('H:i') : '' }}</td>
                <td>{{ minutesToTimeFormat($totalRestMinutes) }}</td>
                <td>{{ minutesToTimeFormat($workMinutes ?? $record?->total_duration) }}</td>
                <td>
                    <a href="{{ $record 
                        ? route('admin.attendance.show', $record->id) 
                        : route('admin.attendance.show', ['id' => $key]) }}"
                        class="detail-link">
                        詳細
                    </a>
                </td>
                </tr>
                @endforeach
        </tbody>
    </table>

    {{-- ===============================
         CSV出力ボタン
    =============================== --}}
    <div class="csv-export">
        <a href="{{ route('admin.attendance.csv', [
            'user_id' => $user->id,
            'month' => $currentMonth->format('Y-m')
        ]) }}" class="csv-btn">
            CSV出力
        </a>
    </div>
</div>
@endsection