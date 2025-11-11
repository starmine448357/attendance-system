@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">

    <h1 class="page-title">勤怠詳細</h1>

    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="detail-table">
            {{-- ===============================
                 名前
            =============================== --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value">{{ $attendance->user->name }}</div>
            </div>

            {{-- ===============================
                 日付
            =============================== --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="value">
                    <span class="date-year">{{ $attendance->date->format('Y年') }}</span>
                    <span class="date-day">{{ $attendance->date->format('n月 j日') }}</span>
                </div>
            </div>

            {{-- ===============================
                 出勤・退勤
            =============================== --}}
            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value center-inputs">
                    <input type="time" name="start_time"
                        value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                    <span>〜</span>
                    <input type="time" name="end_time"
                        value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                </div>

                {{-- ✅ エラーメッセージをこの中に配置 --}}
                @error('start_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error('end_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            {{-- ===============================
                 休憩 全件出力
            =============================== --}}
            @php
            $rests = $attendance->rests ?? collect();
            $displayRests = $rests->count() >= 1 ? $rests : collect([['break_start' => '', 'break_end' => '']]);
            @endphp

            @foreach ($displayRests as $i => $rest)
            <div class="row">
                <div class="label">休憩{{ $i + 1 }}</div>
                <div class="value center-inputs">
                    <input type="time" name="rests[{{ $i }}][break_start]"
                        value="{{ old("rests.$i.break_start", $rest['break_start'] ? \Carbon\Carbon::parse($rest['break_start'])->format('H:i') : '') }}">
                    <span>〜</span>
                    <input type="time" name="rests[{{ $i }}][break_end]"
                        value="{{ old("rests.$i.break_end", $rest['break_end'] ? \Carbon\Carbon::parse($rest['break_end'])->format('H:i') : '') }}">
                </div>

                {{-- ✅ 各休憩行の中にエラー --}}
                @error("rests.$i.break_start")
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error("rests.$i.break_end")
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            @endforeach

            {{-- ===============================
                 備考
            =============================== --}}
            <div class="row">
                <div class="label">備考</div>
                <div class="value">
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                </div>

                {{-- ✅ 備考のエラーも中に --}}
                @error('note')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- ===============================
             ボタン
        =============================== --}}
        <div class="button-area">
            <button type="submit" class="submit-btn">修正</button>
        </div>
    </form>
</div>
@endsection