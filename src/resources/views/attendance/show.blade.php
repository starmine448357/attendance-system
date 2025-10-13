@extends('layouts.user')

@section('title', '勤怠詳細')

@section('content')
<div class="attendance-show">
    <h1 class="page-title">勤怠詳細</h1>

    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ Auth::user()->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ $attendance->date->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>{{ $attendance->start_time?->format('H:i') }} 〜 {{ $attendance->end_time?->format('H:i') }}</td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>{{ $attendance->break_start?->format('H:i') }} 〜 {{ $attendance->break_end?->format('H:i') }}</td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{ $attendance->note ?? 'ー' }}</td>
        </tr>
    </table>

    @if ($attendance->is_pending)
    <p class="note-pending">＊承認待ちのため修正はできません。</p>
    @else
    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>出勤</label>
            <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time?->format('H:i')) }}">
            <span>〜</span>
            <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time?->format('H:i')) }}">
        </div>

        <div class="form-group">
            <label>休憩</label>
            <input type="time" name="break_start" value="{{ old('break_start', $attendance->break_start?->format('H:i')) }}">
            <span>〜</span>
            <input type="time" name="break_end" value="{{ old('break_end', $attendance->break_end?->format('H:i')) }}">
        </div>

        <div class="form-group">
            <label>備考</label>
            <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
        </div>

        <button type="submit" class="btn black">修正</button>
    </form>
    @endif
</div>
@endsection