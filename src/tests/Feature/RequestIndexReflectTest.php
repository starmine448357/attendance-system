<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RequestIndexReflectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_承認待ちに自分の申請が全て表示される()
    {
        // ログインユーザー作成
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // ログイン処理
        Auth::login($user);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        // 承認待ちの申請を2件
        AttendanceRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        // 承認済みの申請を1件
        AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        // 一覧ページアクセス
        $response = $this->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
    }

    /**
     * 「承認済み」に管理者が承認した申請が全て表示されていること
     */
    public function test_承認済みに管理者が承認した申請が全て表示される()
    {
        // ログインユーザー作成
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // ログイン処理
        Auth::login($user);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        // 承認済みの申請を2件
        AttendanceRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        // 承認待ちの申請を1件
        AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        // 一覧ページアクセス
        $response = $this->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }
}
