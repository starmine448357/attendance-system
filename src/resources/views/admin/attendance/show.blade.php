@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    @php
    // 申請データ（最新1件）を取得
    $latestRequest = $attendance->requests->sortByDesc('created_at')->first();
    @endphp

    {{-- ===============================
         閲覧専用（承認待ち or 承認済み）
    =============================== --}}
    @if ($latestRequest && $latestRequest->status === 'pending')
    <div class="detail-table">
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $attendance->user->name }}</div>
        </div>

        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                <span class="date-year">{{ $attendance->date->format('Y年') }}</span>
                <span class="date-day">{{ $attendance->date->format('n月 j日') }}</span>
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value center-inputs">
                <span class="value-time">{{ $latestRequest->start_time ? \Carbon\Carbon::parse($latestRequest->start_time)->format('H:i') : '--:--' }}</span>
                <span>〜</span>
                <span class="value-time">{{ $latestRequest->end_time ? \Carbon\Carbon::parse($latestRequest->end_time)->format('H:i') : '--:--' }}</span>
            </div>
        </div>

        {{-- 休憩1 --}}
        <div class="row">
            <div class="label">休憩1</div>
            <div class="value center-inputs">
                <span class="value-time">{{ $latestRequest->break_start ? \Carbon\Carbon::parse($latestRequest->break_start)->format('H:i') : '--:--' }}</span>
                <span>〜</span>
                <span class="value-time">{{ $latestRequest->break_end ? \Carbon\Carbon::parse($latestRequest->break_end)->format('H:i') : '--:--' }}</span>
            </div>
        </div>

        {{-- 休憩2 --}}
        <div class="row">
            <div class="label">休憩2</div>
            <div class="value center-inputs">
                <span class="value-time">{{ $latestRequest->break_start_2 ? \Carbon\Carbon::parse($latestRequest->break_start_2)->format('H:i') : '--:--' }}</span>
                <span>〜</span>
                <span class="value-time">{{ $latestRequest->break_end_2 ? \Carbon\Carbon::parse($latestRequest->break_end_2)->format('H:i') : '--:--' }}</span>
            </div>
        </div>

        <div class="row">
            <div class="label">備考</div>
            <div class="value">{{ $latestRequest->note }}</div>
        </div>
    </div>

    <p class="pending-message">※承認待ちのため修正はできません。</p>

    {{-- ===============================
         承認済み（勤怠反映後・閲覧専用）
    =============================== --}}
    @elseif ($latestRequest && $latestRequest->status === 'approved')
    <div class="detail-table">
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $attendance->user->name }}</div>
        </div>

        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                <span class="date-year">{{ $attendance->date->format('Y年') }}</span>
                <span class="date-day">{{ $attendance->date->format('n月 j日') }}</span>
            </div>
        </div>

        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value center-inputs">
                <span class="value-time">{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '--:--' }}</span>
                <span>〜</span>
                <span class="value-time">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '--:--' }}</span>
            </div>
        </div>

        @foreach ($attendance->rests as $i => $rest)
        <div class="row">
            <div class="label">休憩{{ $i + 1 }}</div>
            <div class="value center-inputs">
                <span class="value-time">{{ $rest->break_start ? \Carbon\Carbon::parse($rest->break_start)->format('H:i') : '--:--' }}</span>
                <span>〜</span>
                <span class="value-time">{{ $rest->break_end ? \Carbon\Carbon::parse($rest->break_end)->format('H:i') : '--:--' }}</span>
            </div>
        </div>
        @endforeach

        <div class="row">
            <div class="label">備考</div>
            <div class="value">{{ $attendance->note }}</div>
        </div>
    </div>

    <p class="pending-message">※承認済みのため修正はできません。</p>

    {{-- ===============================
         通常（未申請 → 管理者が直接修正）
    =============================== --}}
    @else
    <form method="POST" action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}">
        @csrf
        @method('PUT')

        <div class="detail-table">
            <div class="row">
                <div class="label">名前</div>
                <div class="value">{{ $attendance->user->name }}</div>
            </div>

            <div class="row">
                <div class="label">日付</div>
                <div class="value">
                    <span class="date-year">{{ $attendance->date->format('Y年') }}</span>
                    <span class="date-day">{{ $attendance->date->format('n月 j日') }}</span>
                </div>
            </div>

            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value center-inputs">
                    <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                    <span>〜</span>
                    <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                </div>
            </div>

            @php
            $rests = $attendance->rests ?? collect();
            $displayRests = $rests->count() >= 2 ? $rests : collect(array_pad($rests->toArray(), 2, ['break_start' => '', 'break_end' => '']));
            @endphp

            @foreach ($displayRests as $i => $rest)
            <div class="row">
                <div class="label">休憩{{ $i + 1 }}</div>
                <div class="value center-inputs">
                    <input type="time" name="rests[{{ $i }}][break_start]" value="{{ $rest['break_start'] ? \Carbon\Carbon::parse($rest['break_start'])->format('H:i') : '' }}">
                    <span>〜</span>
                    <input type="time" name="rests[{{ $i }}][break_end]" value="{{ $rest['break_end'] ? \Carbon\Carbon::parse($rest['break_end'])->format('H:i') : '' }}">
                </div>
            </div>
            @endforeach

            <div class="row">
                <div class="label">備考</div>
                <div class="value">
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="submit-btn">修正</button>
    </form>
    @endif
</div>
@endsection