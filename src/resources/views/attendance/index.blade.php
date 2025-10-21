@extends('layouts.user')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
@endsection

@section('content')
<div class="attendance-index">
    <h1 class="page-title">勤怠一覧</h1>

    {{-- 月移動 --}}
    <div class="month-navigation">
        <button class="month-btn">&lt; 前月</button>
        <span class="month-label">{{ now()->format('Y/m') }}</span>
        <button class="month-btn">翌月 &gt;</button>
    </div>

    {{-- テーブル --}}
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
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->format('m/d(D)') }}</td>
                <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                <td>{{ $attendance->break_duration ?? '0:00' }}</td>
                <td>{{ $attendance->total_duration ?? '0:00' }}</td>
                <td><a href="{{ route('attendance.show', $attendance->id) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection