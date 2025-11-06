<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ===== 他のSeederを呼び出し =====
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
        ]);

        // ===== テスト用ユーザー（任意） =====
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
