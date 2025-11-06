<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 修正申請が正常に保存される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'rests' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'note' => 'テスト申請',
        ]);

        $response->assertRedirect(route('request.index', ['status' => 'pending']));
        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'note' => 'テスト申請',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function 備考が未入力の場合はエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'rests' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note']);
    }

    /** @test */
    public function 出勤時間が退勤時間より後ならエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '18:00',
            'end_time'   => '09:00',
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['end_time']);
    }

    /** @test */
    public function 休憩開始が出勤前ならエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'rests' => [
                ['break_start' => '08:00', 'break_end' => '09:30'],
            ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['rests.0.break_start']);
    }

    /** @test */
    public function 休憩開始が退勤後ならエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'rests' => [
                ['break_start' => '19:00', 'break_end' => '20:00'],
            ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['rests.0.break_start']);
    }

    /** @test */
    public function 休憩終了が退勤時間より後ならエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('request.store'), [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'rests' => [
                ['break_start' => '17:00', 'break_end' => '19:00'],
            ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['rests.0.break_end']);
    }
}
