<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test メールアドレスが未入力の場合、エラーメッセージを表示する */
    public function it_fails_when_email_is_missing()
    {
        $response = $this->post('/admin/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください。',
        ]);
    }

    /** @test パスワードが未入力の場合、エラーメッセージを表示する */
    public function it_fails_when_password_is_missing()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください。',
        ]);
    }

    /** @test 登録内容と一致しない場合、エラーメッセージを表示する */
    public function it_fails_when_credentials_are_incorrect()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'invalid123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません。',
        ]);
    }

    /** @test 正しい情報でログイン成功し、勤怠一覧に遷移する */
    public function it_logs_in_successfully_with_valid_credentials()
    {
        // テスト用の管理者を作成
        $admin = \App\Models\Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.attendance.index'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /** @test ログイン後にログアウトできる */
    public function it_can_logout_successfully()
    {
        $admin = \App\Models\Admin::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post('/admin/logout');
        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }
}
