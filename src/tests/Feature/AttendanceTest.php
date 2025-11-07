<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時刻が現在時刻で記録される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::create(2025, 11, 5, 9, 0, 0));

        $response = $this->post('/attendance/store', ['type' => 'start']);
        $response->assertRedirect('/attendance/record');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2025-11-05 00:00:00',
            'start_time' => '2025-11-05 09:00:00',
            'status' => '出勤中',
        ]);
    }

    /** @test */
    public function 出勤中に休憩ボタンを押すとステータスが休憩中になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::create(2025, 11, 5, 9, 0, 0));
        $this->post('/attendance/store', ['type' => 'start']);

        Carbon::setTestNow(Carbon::create(2025, 11, 5, 12, 0, 0));
        $response = $this->post('/attendance/store', ['type' => 'break_start']);
        $response->assertRedirect('/attendance/record');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => '休憩中',
        ]);
    }

    /** @test */
    public function 休憩終了ボタンを押すとステータスが出勤中に戻る()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::create(2025, 11, 5, 9, 0, 0));
        $this->post('/attendance/store', ['type' => 'start']);

        Carbon::setTestNow(Carbon::create(2025, 11, 5, 12, 0, 0));
        $this->post('/attendance/store', ['type' => 'break_start']);

        Carbon::setTestNow(Carbon::create(2025, 11, 5, 12, 30, 0));
        $response = $this->post('/attendance/store', ['type' => 'break_end']);
        $response->assertRedirect('/attendance/record');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => '出勤中',
        ]);
    }

    /** @test */
    public function 出勤中に退勤ボタンを押すとステータスが退勤済になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::create(2025, 11, 5, 9, 0, 0));
        $this->post('/attendance/store', ['type' => 'start']);

        Carbon::setTestNow(Carbon::create(2025, 11, 5, 18, 0, 0));
        $response = $this->post('/attendance/store', ['type' => 'end']);
        $response->assertRedirect('/attendance/record');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => '退勤済',
            'end_time' => '2025-11-05 18:00:00',
        ]);
    }

    /** @test */
    public function 勤怠一覧画面に自分の勤怠情報のみが表示される()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'date' => Carbon::create(2025, 11, 1),
            'status' => '出勤中',
            'start_time' => '2025-11-01 09:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $userB->id,
            'date' => Carbon::create(2025, 11, 1),
            'status' => '出勤中',
            'start_time' => '2025-11-01 10:00:00',
        ]);

        $this->actingAs($userA);
        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // ✅ 自分の出勤時刻は表示される
        $response->assertSee('09:00');
        // ✅ 他人の出勤時刻は表示されない
        $response->assertDontSee('10:00');
    }

    /** @test */
    public function 勤怠一覧を開くと現在の月が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow(Carbon::create(2025, 11, 5));

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('2025年11月');
    }

    /** @test */
    public function 前月ボタンで前月の勤怠一覧が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance?month=2025-10');
        $response->assertStatus(200);
        $response->assertSee('2025年10月');
    }

    /** @test */
    public function 翌月ボタンで翌月の勤怠一覧が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance?month=2025-12');
        $response->assertStatus(200);
        $response->assertSee('2025年12月');
    }

    /** @test */
    public function 詳細ボタンを押すと勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 11, 5),
        ]);

        $this->actingAs($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }

    /** @test */
    public function 勤怠打刻画面で現在の日時が正しく表示される()
    {
        // 現在時刻を固定（テストの再現性を保証）
        Carbon::setTestNow(Carbon::create(2025, 11, 7, 10, 30, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        // 打刻画面にアクセス
        $response = $this->get('/attendance/record');
        $response->assertStatus(200);

        $expectedDate = Carbon::now()->format('Y年 n月 j日（D）');
        $expectedTime = Carbon::now()->format('H:i');

        // 画面上に現在日時が含まれているか確認
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
    /** @test */
    public function 勤務外の場合_ステータスが勤務外と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/attendance/record');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合_ステータスが出勤中と表示される()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance/record');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合_ステータスが休憩中と表示される()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => '休憩中',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance/record');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合_ステータスが退勤済と表示される()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => '退勤済',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance/record');
        $response->assertSee('退勤済');
    }
    /** @test */
    public function 出勤ボタンを押すとステータスが出勤中になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/record');
        $response->assertSee('出勤');

        $this->post('/attendance/store', ['type' => 'start']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
    }

    /** @test */
    /** @test */
    public function 出勤は一日一回しか登録されない()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow(Carbon::create(2025, 11, 7, 9, 0, 0));
        $this->post('/attendance/store', ['type' => 'start']);

        // 二度目の出勤を試みる
        Carbon::setTestNow(Carbon::create(2025, 11, 7, 10, 0, 0));
        $this->post('/attendance/store', ['type' => 'start']);

        // ✅ 出勤レコードは1件しか存在しない
        $this->assertDatabaseCount('attendances', 1);

        // ✅ ステータスは出勤中のまま
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
    }
    /** @test */
    public function 出勤時刻が勤怠一覧画面に反映される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        Carbon::setTestNow(Carbon::create(2025, 11, 7, 9, 0, 0));
        $this->post('/attendance/store', ['type' => 'start']);

        // 一覧を確認
        $response = $this->get('/attendance');
        $response->assertSee('09:00');
    }
}
