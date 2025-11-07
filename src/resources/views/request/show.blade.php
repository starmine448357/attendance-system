@extends('layouts.user')

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
            <div class="value">{{ Auth::user()->name }}</div>
        </div>

        {{-- ===============================
             日付
        =============================== --}}
        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                <span class="date-year">{{ $requestData->attendance->date->format('Y年') }}</span>
                <span class="date-day">{{ $requestData->attendance->date->format('n月 j日') }}</span>
            </div>
        </div>

        {{-- ===============================
             出勤・退勤
        =============================== --}}
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value center-inputs">
                <span>{{ $requestData->start_time ? \Carbon\Carbon::parse($requestData->start_time)->format('H:i') : '―' }}</span>
                <span>〜</span>
                <span>{{ $requestData->end_time ? \Carbon\Carbon::parse($requestData->end_time)->format('H:i') : '―' }}</span>
            </div>
        </div>

        {{-- ===============================
             休憩（全件表示）
        =============================== --}}
        @php
        $displayRests = collect([
        ['break_start' => $requestData->break_start, 'break_end' => $requestData->break_end],
        ['break_start' => $requestData->break_start_2, 'break_end' => $requestData->break_end_2],
        ]);

        if (!empty($requestData->extra_rests_json)) {
        $extraRests = json_decode($requestData->extra_rests_json, true);
        foreach ($extraRests as $rest) {
        $displayRests->push([
        'break_start' => $rest['break_start'] ?? null,
        'break_end' => $rest['break_end'] ?? null,
        ]);
        }
        }
        @endphp

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

        {{-- ===============================
             備考
        =============================== --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value">
                {{ $requestData->note ?? '―' }}
            </div>
        </div>
    </div>

    {{-- ===============================
         承認ステータス表示
    =============================== --}}
    @if ($requestData->status === 'pending')
    <p class="pending-message">※承認待ちのため修正はできません。</p>
    @elseif ($requestData->status === 'approved')
    <p class="approved-message">※承認済みの申請です。</p>
    @endif
</div>
@endsection