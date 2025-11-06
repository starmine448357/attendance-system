@extends('layouts.admin')

@section('title', '申請詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-request-show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    <div class="detail-table">
        {{-- ===== 名前 ===== --}}
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $request->attendance->user->name ?? '―' }}</div>
        </div>

        {{-- ===== 日付 ===== --}}
        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                <span class="date-year">{{ $request->attendance->date->format('Y年') }}</span>
                <span class="date-day">{{ $request->attendance->date->format('n月 j日') }}</span>
            </div>
        </div>

        {{-- ===== 出勤・退勤 ===== --}}
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value center-inputs">
                <span>{{ $request->start_time ? \Carbon\Carbon::parse($request->start_time)->format('H:i') : '―' }}</span>
                <span>〜</span>
                <span>{{ $request->end_time ? \Carbon\Carbon::parse($request->end_time)->format('H:i') : '―' }}</span>
            </div>
        </div>

        {{-- ===== 休憩（常に2件表示） ===== --}}
        @php
        $displayRests = collect([
        ['break_start' => $request->break_start, 'break_end' => $request->break_end],
        ['break_start' => $request->break_start_2, 'break_end' => $request->break_end_2],
        ]);
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

        {{-- ===== 備考 ===== --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value">{{ $request->note ?? '―' }}</div>
        </div>
    </div>

    {{-- ===== 承認ボタンエリア ===== --}}
    <div class="button-area">
        @if ($request->status === 'pending')
        <a href="{{ route('admin.request.approve', $request->id) }}" class="approve-btn">
            承認
        </a>
        @else
        <span class="approved-btn">承認済み</span>
        @endif
    </div>
</div>
@endsection