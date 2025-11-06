@extends('layouts.admin')

@section('title', '申請詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-request-show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    <div class="detail-table">
        {{-- ===============================
             名前
        =============================== --}}
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $request->attendance->user->name ?? '―' }}</div>
        </div>

        {{-- ===============================
             日付
        =============================== --}}
        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                <span class="date-year">{{ $request->attendance->date->format('Y年') }}</span>
                <span class="date-day">{{ $request->attendance->date->format('n月 j日') }}</span>
            </div>
        </div>

        {{-- ===============================
             出勤・退勤
        =============================== --}}
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value center-inputs">
                <span>{{ $request->start_time ? \Carbon\Carbon::parse($request->start_time)->format('H:i') : '―' }}</span>
                <span>〜</span>
                <span>{{ $request->end_time ? \Carbon\Carbon::parse($request->end_time)->format('H:i') : '―' }}</span>
            </div>
        </div>

        {{-- ===============================
     休憩（全件表示）
=============================== --}}
        @php
        $displayRests = collect([
        ['break_start' => $request->break_start, 'break_end' => $request->break_end],
        ['break_start' => $request->break_start_2, 'break_end' => $request->break_end_2],
        ]);

        // ✅ extra_rests_json が存在すればデコードして追加
        if (!empty($request->extra_rests_json)) {
        $extraRests = json_decode($request->extra_rests_json, true);
        foreach ($extraRests as $rest) {
        $displayRests->push([
        'break_start' => $rest['break_start'] ?? null,
        'break_end' => $rest['break_end'] ?? null,
        ]);
        }
        }
        @endphp

        {{-- ✅ すべての休憩をループで表示 --}}
        @foreach ($displayRests as $i => $rest)
        <div class="row">
            <div class="label">休憩{{ $i + 1 }}</div>
            <div class="value center-inputs">
                <span>{{ $rest['break_start'] ? \Carbon\Carbon::parse($rest['break_start'])->format('H:i') : '―' }}</span>
                <span>〜</span>
                <span>{{ $rest['break_end'] ? \Carbon\Carbon::parse($rest['break_end'])->format('H:i') : '―' }}</span>
            </div>
        </div>
        @endforeach
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

    {{-- ===============================
         承認ボタンエリア
    =============================== --}}
    <div class="button-area">
        @if ($request->status === 'pending')
        {{-- ✅ 承認待ちのとき --}}
        <form method="POST" action="{{ route('admin.request.approve', $request->id) }}">
            @csrf
            <button type="submit" class="approve-btn">承認</button>
        </form>
        @else
        {{-- ✅ 承認済みのとき --}}
        <span class="approved-btn">承認済み</span>
        @endif
    </div>
</div>
@endsection