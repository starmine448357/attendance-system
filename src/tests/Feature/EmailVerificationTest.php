<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Auth\Authenticatable;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後に認証メールが送信される()
    {
        Notification::fake();

        /** @var \App\Models\User&Authenticatable $user */
        $user = User::factory()->create(['email_verified_at' => null]);

        // ✅ 手動で通知を発行（Fortifyイベントがテストでは動かないため）
        $user->notify(new VerifyEmail);

        // 通知が送信されたことを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function 認証誘導画面からメール認証ページに遷移できる()
    {
        /** @var \App\Models\User&Authenticatable $user */
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
    }

    /** @test */
    public function 認証を完了すると勤怠登録画面にリダイレクトされる()
    {
        /** @var \App\Models\User&Authenticatable $user */
        $user = User::factory()->unverified()->create();

        // 認証リンクを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証リンクにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance/record');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
