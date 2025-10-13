<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| ここでは Fortify がログイン / 登録 / ログアウトなどを管理します。
| スタッフ用の勤怠機能はログイン後にアクセスできるようにしています。
|
*/

// ホーム（初期アクセス時のリダイレクト処理）
Route::get('/', function () {
    // もしログイン済みなら打刻画面へ
    if (auth()->check()) {
        return redirect('/attendance/record');
    }
    // 未ログインなら Fortify のログイン画面へ
    return redirect('/login');
});

// スタッフ認証後のみアクセスできるルート
Route::middleware(['auth'])->group(function () {

    // 打刻ページ（出勤・休憩・退勤）
    Route::get('/attendance/record', [AttendanceController::class, 'record'])->name('attendance.record');

    // 勤怠一覧
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細（申請もここから）
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');

    // 申請一覧
    Route::get('/request', [RequestController::class, 'index'])->name('request.index');

    // 勤怠修正申請送信
    Route::post('/request', [RequestController::class, 'store'])->name('request.store');
});

// 打刻データ送信（仮）
Route::post('/attendance', function () {
    return back()->with('message', '打刻データを受け取りました（仮）');
})->name('attendance.store');
