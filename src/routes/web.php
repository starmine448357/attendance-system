<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RegisteredUserController;

// 管理者用コントローラ
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\Admin\AdminLoginController;


/*
|--------------------------------------------------------------------------
| Fortify 会員登録 上書き
|--------------------------------------------------------------------------
*/

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');

/*
|--------------------------------------------------------------------------
| ✅ Fortify ログイン 上書き（日本語バリデーション対応）
|--------------------------------------------------------------------------
*/
Route::post('/login', function (LoginRequest $request) {

    // バリデーションは LoginRequest 内のルール・メッセージが適用される

    if (!Auth::attempt($request->only('email', 'password'))) {
        throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
        ]);
    }

    $request->session()->regenerate();
    return redirect()->intended('/attendance/record');
});

/*
|--------------------------------------------------------------------------
| メール認証誘導画面（未ログインでも可）
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', fn() => view('auth.verify-email'))
    ->name('verification.notice');

/*
|--------------------------------------------------------------------------
| 未ログイン状態での認証メール再送
|--------------------------------------------------------------------------
*/
Route::post('/email/verify/resend-guest', function () {
    $userId = session('unauth_user_id');

    if (!$userId) {
        $user = session('unauthenticated_user');
        if ($user && method_exists($user, 'getKey')) {
            $userId = $user->getKey();
        }
    }

    if (!$userId) {
        return back()->withErrors(['resend' => '再送できませんはじめから登録し直してください']);
    }

    $user = User::find($userId);

    if (!$user) {
        return back()->withErrors(['resend' => 'ユーザーが見つかりません再登録してください']);
    }

    if ($user->hasVerifiedEmail()) {
        return redirect('/login')->with('status', 'すでに認証済みですログインしてください');
    }

    $user->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->name('verification.resend.guest')->middleware(['guest', 'throttle:6,1']);

/*
|--------------------------------------------------------------------------
| 初期アクセス時のリダイレクト
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect('/attendance/record')
        : redirect('/login');
});

/*
|--------------------------------------------------------------------------
| 一般ユーザー（スタッフ）用ルート（ログイン＋メール認証必須）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 打刻ページ（出勤・休憩・退勤）
    Route::get('/attendance/record', [AttendanceController::class, 'record'])
        ->name('attendance.record');

    // 打刻処理
    Route::post('/attendance/store', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    // 勤怠一覧ページ
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 勤怠詳細画面（一般ユーザー）
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.detail');
    // 修正申請一覧ページ

    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])
        ->name('request.index');

    // 修正申請登録（POST）
    Route::post('/stamp_correction_request/store', [RequestController::class, 'store'])
        ->name('request.store');

    // ✅ 修正申請詳細ページ（閲覧専用）
    Route::get('/stamp_correction_request/show/{id}', [RequestController::class, 'show'])
        ->name('request.show');
});

/*
|--------------------------------------------------------------------------
| 管理者用ルート（/admin 以下）
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // ===== 管理者ログイン =====
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])
        ->name('login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])
        ->name('logout');

    // ===== 管理者認証後のみアクセス可能 =====
    Route::middleware(['auth:admin'])->group(function () {

        // 日次勤怠一覧
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
            ->name('attendance.index');

        // 勤怠詳細（表示）
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
            ->name('attendance.show');

        // 勤怠詳細（更新）
        Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])
            ->name('attendance.update');

        // スタッフ一覧
        Route::get('/staff/list', [AdminStaffController::class, 'index'])
            ->name('staff.index');

        // 特定スタッフの月次勤怠
        Route::get('/attendance/monthly/{user_id}', [AdminAttendanceController::class, 'monthly'])
            ->name('attendance.monthly');

        // ✅ 月次勤怠CSV出力（追加）
        Route::get('/attendance/csv/{user_id}/{month}', [AdminAttendanceController::class, 'exportCsv'])
            ->name('attendance.csv');

        // 修正申請一覧
        Route::get('/stamp_correction_request/list', [AdminRequestController::class, 'index'])
            ->name('request.index');

        // 修正申請詳細
        Route::get('/stamp_correction_request/show/{id}', [AdminRequestController::class, 'show'])
            ->name('request.show');

        // 修正申請承認
        Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'approve'])
            ->name('request.approve');
    });
});
