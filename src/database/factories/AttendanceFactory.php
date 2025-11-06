<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => '出勤中',
            'note' => 'テスト用勤怠',
        ];
    }
}
