@extends('layouts.user')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('content')

@php
use Carbon\Carbon;
$targetDate = $attendance?->date ?? Carbon::parse(request()->route('id'));
@endphp

<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    {{-- ===============================
         新規申請モード（未出勤日）
    =============================== --}}
    @if ($attendance === null)
    <form method="POST" action="{{ route('request.store') }}" novalidate>
        @csrf
        <input type="hidden" name="date" value="{{ $targetDate->format('Y-m-d') }}">

        <div class="detail-table">
            {{-- 名前 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value">{{ Auth::user()->name }}</div>
            </div>

            {{-- 日付 --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="value">
                    <span class="date-year">{{ $targetDate->format('Y年') }}</span>
                    <span class="date-day">{{ $targetDate->format('n月 j日') }}</span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value center-inputs">
                    <input type="time" name="start_time" value="{{ old('start_time') }}">
                    <span>〜</span>
                    <input type="time" name="end_time" value="{{ old('end_time') }}">
                </div>
                @error('start_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error('end_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            {{-- 休憩1・2固定 --}}
            @for ($i = 0; $i < 2; $i++)
                <div class="row">
                <div class="label">休憩{{ $i + 1 }}</div>
                <div class="value center-inputs">
                    <input type="time" name="rests[{{ $i }}][break_start]" value="{{ old("rests.$i.break_start") }}">
                    <span>〜</span>
                    <input type="time" name="rests[{{ $i }}][break_end]" value="{{ old("rests.$i.break_end") }}">
                </div>
                @error("rests.$i.break_start")
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error("rests.$i.break_end")
                <div class="error-message">{{ $message }}</div>
                @enderror
        </div>
        @endfor

        {{-- 備考 --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value">
                <textarea name="note">{{ old('note') }}</textarea>
            </div>
            @error('note')
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>
</div>

<button type="submit" class="submit-btn">申請</button>
</form>

{{-- ===============================
         修正申請モード（既存勤怠）
    =============================== --}}
@else
<form method="POST" action="{{ route('request.store') }}" novalidate>
    @csrf
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

    <div class="detail-table">
        {{-- 名前 --}}
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $attendance->user->name }}</div>
        </div>

        {{-- 日付 --}}
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
                <input type="time" name="start_time"
                    value="{{ old('start_time', $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                <span>〜</span>
                <input type="time" name="end_time"
                    value="{{ old('end_time', $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
            </div>
            @error('start_time')
            <div class="error-message">{{ $message }}</div>
            @enderror
            @error('end_time')
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- 休憩1・2固定 --}}
        @php
        $rests = $attendance->rests ?? collect();
        $displayRests = $rests->count() >= 2
        ? $rests->values()
        : collect(array_pad($rests->toArray(), 2, ['break_start' => '', 'break_end' => '']));
        @endphp

        @foreach ($displayRests as $i => $rest)
        <div class="row">
            <div class="label">休憩{{ $i + 1 }}</div>
            <div class="value center-inputs">
                <input type="time" name="rests[{{ $i }}][break_start]"
                    value="{{ old("rests.$i.break_start", $rest['break_start'] ? Carbon::parse($rest['break_start'])->format('H:i') : '') }}">
                <span>〜</span>
                <input type="time" name="rests[{{ $i }}][break_end]"
                    value="{{ old("rests.$i.break_end", $rest['break_end'] ? Carbon::parse($rest['break_end'])->format('H:i') : '') }}">
            </div>
            @error("rests.$i.break_start")
            <div class="error-message">{{ $message }}</div>
            @enderror
            @error("rests.$i.break_end")
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>
        @endforeach

        {{-- 備考 --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value">
                <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
            </div>
            @error('note')
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <button type="submit" class="submit-btn">修正</button>
</form>
@endif
</div>
@endsection