<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // 各従業員に3日分の勤怠データを作成
            for ($d = 0; $d < 30; $d++) {
                $date = Carbon::today()->subDays($d);

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'start_time' => '09:00:00',
                    'end_time'   => '18:00:00',
                    'status'     => '退勤済',
                    'break_duration' => 60,
                    'total_duration' => 480,
                ]);

                // 従業員1だけ3回休憩、それ以外は2回
                if ($user->id === 1) {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'order' => 1,
                        'break_start' => '10:00:00',
                        'break_end' => '10:15:00',
                    ]);
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'order' => 2,
                        'break_start' => '13:00:00',
                        'break_end' => '13:30:00',
                    ]);
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'order' => 3,
                        'break_start' => '16:00:00',
                        'break_end' => '16:10:00',
                    ]);
                } else {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'order' => 1,
                        'break_start' => '12:00:00',
                        'break_end' => '12:30:00',
                    ]);
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'order' => 2,
                        'break_start' => '15:00:00',
                        'break_end' => '15:15:00',
                    ]);
                }
            }
        }
    }
}
