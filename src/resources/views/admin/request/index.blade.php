@extends('layouts.user')

@section('title', '申請一覧')

@section('content')
<div class="request-index">
    <h1 class="page-title">申請一覧</h1>

    {{-- タブ切替 --}}
    <div class="tab-menu">
        <a href="?status=pending" class="tab {{ request('status') !== 'approved' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=approved" class="tab {{ request('status') === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    {{-- テーブル --}}
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $req)
            <tr>
                <td>{{ $req->status_label }}</td>
                <td>{{ $req->user->name }}</td>
                <td>{{ $req->target_date->format('Y/m/d') }}</td>
                <td>{{ $req->reason }}</td>
                <td>{{ $req->created_at->format('Y/m/d') }}</td>
                <td><a href="{{ route('attendance.show', $req->attendance_id) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsectionmodottayo