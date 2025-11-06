<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "従業員{$i}",
                'email' => "employee{$i}@example.com",
                'password' => Hash::make('password123'),
                'email_verified_at' => now(), // 認証済み状態にする
            ]);
        }
    }
}
