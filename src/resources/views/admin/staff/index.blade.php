@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-index.css') }}">
@endsection

@section('content')
<div class="staff-index">
    <h1 class="page-title">スタッフ一覧</h1>

    {{-- ===============================
         スタッフテーブル
    =============================== --}}
    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffs as $staff)
            <tr>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->email }}</td>
                <td>
                    <a href="{{ route('admin.attendance.monthly', ['user_id' => $staff->id]) }}" class="detail-link">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection