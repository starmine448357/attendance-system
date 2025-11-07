<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;

class AttendanceRequestFactory extends Factory
{
    protected $model = AttendanceRequest::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
