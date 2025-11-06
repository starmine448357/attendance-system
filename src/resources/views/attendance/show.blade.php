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
            <div class="row">
                <div class="label">名前</div>
                <div class="value">{{ Auth::user()->name }}</div>
            </div>

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
                    @error('start_time')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('end_time')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- 休憩1・2固定 --}}
            @for ($i = 0; $i < 2; $i++)
                <div class="row">
                <div class="label">休憩{{ $i + 1 }}</div>
                <div class="value center-inputs">
                    <input type="time" name="rests[{{ $i }}][break_start]" value="{{ old("rests.$i.break_start") }}">
                    <span>〜</span>
                    <input type="time" name="rests[{{ $i }}][break_end]" value="{{ old("rests.$i.break_end") }}">
                    @error("rests.$i.break_start")
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error("rests.$i.break_end")
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
        </div>
        @endfor

        {{-- 備考 --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value center-inputs">
                <textarea name="note">{{ old('note') }}</textarea>
                @error('note')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>
</div>

<button type="submit" class="submit-btn">申請</button>
</form>

{{-- ===============================
         承認待ち
    =============================== --}}
@elseif ($pendingRequest)
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
            <span>{{ $pendingRequest->start_time ? Carbon::parse($pendingRequest->start_time)->format('H:i') : '―' }}</span>
            <span>〜</span>
            <span>{{ $pendingRequest->end_time ? Carbon::parse($pendingRequest->end_time)->format('H:i') : '―' }}</span>
        </div>
    </div>

    {{-- 休憩（申請内容から反映） --}}
    @php
    $rests = [];
    if ($pendingRequest->break_start || $pendingRequest->break_end) {
    $rests[] = ['break_start' => $pendingRequest->break_start, 'break_end' => $pendingRequest->break_end];
    }
    if ($pendingRequest->break_start_2 || $pendingRequest->break_end_2) {
    $rests[] = ['break_start' => $pendingRequest->break_start_2, 'break_end' => $pendingRequest->break_end_2];
    }
    if (!empty($pendingRequest->extra_rests_json)) {
    $extra = json_decode($pendingRequest->extra_rests_json, true);
    $rests = array_merge($rests, $extra);
    }
    @endphp

    @foreach ($rests as $i => $rest)
    <div class="row">
        <div class="label">休憩{{ $i + 1 }}</div>
        <div class="value center-inputs">
            <span>{{ $rest['break_start'] ? Carbon::parse($rest['break_start'])->format('H:i') : '―' }}</span>
            <span>〜</span>
            <span>{{ $rest['break_end'] ? Carbon::parse($rest['break_end'])->format('H:i') : '―' }}</span>
        </div>
    </div>
    @endforeach

    {{-- 備考 --}}
    <div class="row">
        <div class="label">備考</div>
        <div class="value">{{ $pendingRequest->note ?? '―' }}</div>
    </div>
</div>
<p class="pending-message">※承認待ちのため修正はできません。</p>

{{-- ===============================
         承認済み（再修正可能）
    =============================== --}}
@elseif ($approvedRequest)
{{-- ✅ 承認済みでも再修正を可能にする --}}
<form method="POST" action="{{ route('request.store') }}" novalidate>
    @csrf
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

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
                <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                <span>〜</span>
                <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                @error('start_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error('end_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 休憩全件 --}}
        @php
        $rests = $attendance->rests ?? collect();
        $displayRests = $rests->count() >= 2 ? $rests->values() : collect(array_pad($rests->toArray(), 2, ['break_start' => '', 'break_end' => '']));
        @endphp

        @foreach ($displayRests as $i => $rest)
        <div class="row">
            <div class="label">休憩{{ $i + 1 }}</div>
            <div class="value center-inputs">
                <input type="time" name="rests[{{ $i }}][break_start]" value="{{ $rest['break_start'] ? Carbon::parse($rest['break_start'])->format('H:i') : '' }}">
                <span>〜</span>
                <input type="time" name="rests[{{ $i }}][break_end]" value="{{ $rest['break_end'] ? Carbon::parse($rest['break_end'])->format('H:i') : '' }}">
                @error("rests.$i.break_start")
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error("rests.$i.break_end")
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @endforeach

        {{-- 備考 --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value center-inputs">
                <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                @error('note')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <button type="submit" class="submit-btn">修正</button>
</form>

{{-- ===============================
         修正可能（通常）
    =============================== --}}
@else
<form method="POST" action="{{ route('request.store') }}" novalidate>
    @csrf
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

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
                <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                <span>〜</span>
                <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                @error('start_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error('end_time')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 休憩全件 --}}
        @php
        $rests = $attendance->rests ?? collect();
        $displayRests = $rests->count() >= 2 ? $rests->values() : collect(array_pad($rests->toArray(), 2, ['break_start' => '', 'break_end' => '']));
        @endphp

        @foreach ($displayRests as $i => $rest)
        <div class="row">
            <div class="label">休憩{{ $i + 1 }}</div>
            <div class="value center-inputs">
                <input type="time" name="rests[{{ $i }}][break_start]" value="{{ $rest['break_start'] ? Carbon::parse($rest['break_start'])->format('H:i') : '' }}">
                <span>〜</span>
                <input type="time" name="rests[{{ $i }}][break_end]" value="{{ $rest['break_end'] ? Carbon::parse($rest['break_end'])->format('H:i') : '' }}">
                @error("rests.$i.break_start")
                <div class="error-message">{{ $message }}</div>
                @enderror
                @error("rests.$i.break_end")
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @endforeach

        {{-- 備考 --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value center-inputs">
                <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                @error('note')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <button type="submit" class="submit-btn">修正</button>
</form>
@endif
</div>
@endsection