<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminRequestApproveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が修正申請を承認するとステータスがapprovedになる()
    {
        // 管理者・ユーザー作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user = User::factory()->create();

        // 勤怠と申請データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-07',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => '退勤済',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '09:30:00',
            'end_time' => '17:30:00',
            'note' => '退勤時間の修正',
            'status' => 'pending',
        ]);

        // 管理者として承認処理を実行
        $this->actingAs($admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/{$request->id}");

        // ✅ ステータスがapprovedに更新されることを確認
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function 承認後に勤怠データが修正申請内容に反映される()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-07',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => '退勤済',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'note' => '出勤・退勤の修正',
            'status' => 'pending',
        ]);

        // 管理者が承認
        $this->actingAs($admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/{$request->id}");

        // ✅ 承認後、attendanceが更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        // ✅ 申請のステータスはapproved
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}
