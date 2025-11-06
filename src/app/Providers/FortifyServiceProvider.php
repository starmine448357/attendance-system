<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Fortify\Contracts\RegisterResponse;

// ✅ 追加
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Features;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 登録直後はメール認証画面へ遷移
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect()->route('verification.notice');
            }
        });
    }

    public function boot(): void
    {
        /* ============================================================
         | 1. ビュー設定
         ============================================================ */
        Fortify::loginView(fn() => view('auth.login'));
        Fortify::registerView(fn() => view('auth.register'));
        Fortify::verifyEmailView(fn() => view('auth.verify-email'));

        /* ============================================================
         | 2. Fortifyログインパイプラインを上書き
         |    （英語→日本語へ完全置き換え）
         ============================================================ */
        Fortify::authenticateUsing(function (Request $request) {
            // ✅ 独自バリデーション（日本語ルールで実施）
            $loginRequest = new LoginRequest();
            validator(
                $request->only('email', 'password'),
                $loginRequest->rules(),
                $loginRequest->messages()
            )->validate();

            // ✅ 認証実行
            if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません。'],
                ]);
            }

            return Auth::user();
        });

        // ✅ Fortifyの英語バリデーションパイプラインをバイパス
        Fortify::loginThrough(function (Request $request) {
            return array_filter([
                // → あなたの authenticateUsing() を直接呼ぶため
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });

        /* ============================================================
         | 3. メール認証ルート
         ============================================================ */
        Route::middleware('web')->group(function () {
            Route::get('/email/verify/{id}/{hash}', function (Request $request) {
                $userId = $request->route('id');
                $user = User::find($userId);

                if (!$user) {
                    return redirect('/login')->withErrors(['email' => 'ユーザーが存在しません。']);
                }

                $expectedHash = sha1($user->getEmailForVerification());
                if (!hash_equals((string)$request->route('hash'), $expectedHash)) {
                    return redirect('/login')->withErrors(['email' => '認証リンクが無効です。']);
                }

                if (!$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                Auth::guard('web')->login($user, true);
                return redirect('/attendance/record');
            })->name('verification.verify');
        });

        /* ============================================================
         | 4. ログイン試行制限（5回/分）
         ============================================================ */
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower($request->email) . '|' . $request->ip()
            );
        });

        /* ============================================================
         | 5. Fortify機能設定
         ============================================================ */
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }
}
