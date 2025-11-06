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

            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value center-inputs">
                    <input type="time" name="start_time" value="{{ old('start_time') }}">
                    <span>〜</span>
                    <input type="time" name="end_time" value="{{ old('end_time') }}">
                </div>
                @error('start_time')
                <p class="error-message">{{ $message }}</p>
                @enderror
                @error('end_time')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            @for ($i = 0; $i < 2; $i++)
                <div class="row">
                <div class="label">休憩{{ $i + 1 }}</div>
                <div class="value center-inputs">
                    <input type="time" name="rests[{{ $i }}][break_start]" value="{{ old("rests.$i.break_start") }}">
                    <span>〜</span>
                    <input type="time" name="rests[{{ $i }}][break_end]" value="{{ old("rests.$i.break_end") }}">
                </div>
                @error("rests.$i.break_start")
                <p class="error-message">{{ $message }}</p>
                @enderror
                @error("rests.$i.break_end")
                <p class="error-message">{{ $message }}</p>
                @enderror
        </div>
        @endfor

        <div class="row">
            <div class="label">備考</div>
            <div class="value">
                <textarea name="note">{{ old('note') }}</textarea>
            </div>
            @error('note')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
</div>

<button type="submit" class="submit-btn">修正</button>
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

    <div class="row">
        <div class="label">出勤・退勤</div>
        <div class="value center-inputs">
            <span>{{ $pendingRequest->start_time ? Carbon::parse($pendingRequest->start_time)->format('H:i') : '―' }}</span>
            <span>〜</span>
            <span>{{ $pendingRequest->end_time ? Carbon::parse($pendingRequest->end_time)->format('H:i') : '―' }}</span>
        </div>
    </div>

    @php
    $displayRests = collect([
    ['break_start' => $pendingRequest->break_start, 'break_end' => $pendingRequest->break_end],
    ['break_start' => $pendingRequest->break_start_2, 'break_end' => $pendingRequest->break_end_2],
    ]);
    @endphp

    @foreach ($displayRests as $i => $rest)
    <div class="row">
        <div class="label">休憩{{ $i + 1 }}</div>
        <div class="value center-inputs">
            <span>{{ $rest['break_start'] ? Carbon::parse($rest['break_start'])->format('H:i') : '―' }}</span>
            <span>〜</span>
            <span>{{ $rest['break_end'] ? Carbon::parse($rest['break_end'])->format('H:i') : '―' }}</span>
        </div>
    </div>
    @endforeach

    <div class="row">
        <div class="label">備考</div>
        <div class="value">{{ $pendingRequest->note ?? '―' }}</div>
    </div>
</div>
<p class="pending-message">※承認待ちのため修正はできません。</p>

{{-- ===============================
         承認済み
    =============================== --}}
@elseif ($approvedRequest)
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
            <span>{{ $approvedRequest->start_time ? Carbon::parse($approvedRequest->start_time)->format('H:i') : '―' }}</span>
            <span>〜</span>
            <span>{{ $approvedRequest->end_time ? Carbon::parse($approvedRequest->end_time)->format('H:i') : '―' }}</span>
        </div>
    </div>

    @php
    $displayRests = collect([
    ['break_start' => $approvedRequest->break_start, 'break_end' => $approvedRequest->break_end],
    ['break_start' => $approvedRequest->break_start_2, 'break_end' => $approvedRequest->break_end_2],
    ]);
    @endphp

    @foreach ($displayRests as $i => $rest)
    <div class="row">
        <div class="label">休憩{{ $i + 1 }}</div>
        <div class="value center-inputs">
            <span>{{ $rest['break_start'] ? Carbon::parse($rest['break_start'])->format('H:i') : '―' }}</span>
            <span>〜</span>
            <span>{{ $rest['break_end'] ? Carbon::parse($rest['break_end'])->format('H:i') : '―' }}</span>
        </div>
    </div>
    @endforeach

    <div class="row">
        <div class="label">備考</div>
        <div class="value">{{ $approvedRequest->note ?? '―' }}</div>
    </div>
</div>
<div class="button-area"><span class="approved-btn">承認済み</span></div>

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

        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value center-inputs">
                <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                <span>〜</span>
                <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
            </div>
            @error('start_time') <p class="error-message">{{ $message }}</p> @enderror
            @error('end_time') <p class="error-message">{{ $message }}</p> @enderror
        </div>

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
            </div>
            @error("rests.$i.break_start") <p class="error-message">{{ $message }}</p> @enderror
            @error("rests.$i.break_end") <p class="error-message">{{ $message }}</p> @enderror
        </div>
        @endforeach

        <div class="row">
            <div class="label">備考</div>
            <div class="value"><textarea name="note">{{ old('note', $attendance->note) }}</textarea></div>
            @error('note') <p class="error-message">{{ $message }}</p> @enderror
        </div>
    </div>

    <button type="submit" class="submit-btn">修正</button>
</form>
@endif
</div>
@endsection