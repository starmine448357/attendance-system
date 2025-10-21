<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RegisteredUserController;

// =====================
// Fortify register 上書き
// =====================
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');

// =====================
// 未ログインでも verify 誘導画面を表示させる
// =====================
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice')->middleware('guest');

// =====================
// 未ログインでも認証メールを再送できる
// =====================
Route::post('/email/verify/resend-guest', function () {
    // 登録時に保存した未認証ユーザーIDを取り出す
    $userId = session('unauth_user_id');

    if (!$userId) {
        // 旧構成との互換（丸ごと格納されてたケース対応）
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

    // すでに認証済みならログインへ誘導
    if ($user->hasVerifiedEmail()) {
        return redirect('/login')->with('status', 'すでに認証済みです。ログインしてください。');
    }

    // 認証メールを再送
    $user->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->name('verification.resend.guest')->middleware(['guest', 'throttle:6,1']);

// =====================
// 初期アクセス振り分け
// =====================
Route::get('/', function () {
    return auth()->check()
        ? redirect('/attendance/record')
        : redirect('/login');
});

// =====================
// ログイン & メール認証済のみ
// =====================
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/attendance/record', [AttendanceController::class, 'record'])
        ->name('attendance.record');

    Route::post('/attendance/store', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.show');

    Route::get('/request', [RequestController::class, 'index'])
        ->name('request.index');

    Route::post('/request/store', [RequestController::class, 'store'])
        ->name('request.store');
});
