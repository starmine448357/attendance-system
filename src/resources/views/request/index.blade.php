@extends('layouts.user')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="request-container">

    <h1 class="page-title">申請一覧</h1>

    {{-- ===============================
         タブ切り替え（承認待ち／承認済み）
    =============================== --}}
    <div class="tab-menu">
        <a href="{{ route('request.index', ['status' => 'pending']) }}"
            class="tab {{ request('status') !== 'approved' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('request.index', ['status' => 'approved']) }}"
            class="tab {{ request('status') === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <hr class="divider">

    {{-- ===============================
         申請テーブル表示
    =============================== --}}
    <div class="table-wrapper">
        @if ($requests->isEmpty())
        <p class="no-data">該当する申請はありません。</p>
        @else
        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $request)
                <tr>
                    <td>
                        {{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}
                    </td>
                    <td>{{ $request->attendance->user->name ?? '―' }}</td>
                    <td>
                        {{ optional($request->attendance)->date
                                    ? $request->attendance->date->format('Y/m/d')
                                    : '―' }}
                    </td>
                    <td>{{ $request->note }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('request.show', $request->id) }}">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection