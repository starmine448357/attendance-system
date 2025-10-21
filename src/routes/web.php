<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RegisteredUserController;

// =====================
// Fortify 会員登録 上書き
// =====================
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');

// =====================
// メール認証誘導画面（未ログインでも可）
// =====================
Route::get('/email/verify', fn() => view('auth.verify-email'))
    ->name('verification.notice');

// =====================
// 未ログイン状態での認証メール再送
// =====================
Route::post('/email/verify/resend-guest', function () {
    $userId = session('unauth_user_id');

    if (!$userId) {
        $user = session('unauthenticated_user');
        if ($user && method_exists($user, 'getKey')) {
            $userId = $user->getKey();
        }
    }
    if (!$userId) {
        return back()->withErrors(['resend' => '再送できません。はじめから登録し直してください。']);
    }

    $user = User::find($userId);
    if (!$user) {
        return back()->withErrors(['resend' => 'ユーザーが見つかりません。再登録してください。']);
    }
    if ($user->hasVerifiedEmail()) {
        return redirect('/login')->with('status', 'すでに認証済みです。ログインしてください。');
    }

    $user->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->name('verification.resend.guest')->middleware(['guest', 'throttle:6,1']);

// =====================
// 初期アクセス
// =====================
Route::get('/', function () {
    return auth()->check()
        ? redirect('/attendance/record')
        : redirect('/login');
});

// =====================
// 一般ユーザー（ログイン ＋ メール認証）
// =====================
Route::middleware(['auth', 'verified'])->group(function () {

    // 打刻
    Route::get('/attendance/record', [AttendanceController::class, 'record'])->name('attendance.record');
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧・詳細
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');

    // 修正申請
    Route::get('/request', [RequestController::class, 'index'])->name('request.index');
    Route::post('/request', [RequestController::class, 'store'])->name('request.store');
});
