<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fails_when_name_is_missing()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください。']);
    }

    /** @test */
    public function it_fails_when_email_is_invalid()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスの形式が正しくありません。']);
    }

    /** @test */
    public function it_fails_when_password_is_too_short()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'short@example.com',
            'password' => '123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください。']);
    }

    /** @test */
    public function it_registers_successfully_with_valid_data()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'valid@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'valid@example.com']);
    }
}
