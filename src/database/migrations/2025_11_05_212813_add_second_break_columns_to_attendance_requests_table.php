<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            // 2つ目の休憩用カラムを追加
            $table->time('break_start_2')->nullable()->after('break_end');
            $table->time('break_end_2')->nullable()->after('break_start_2');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->dropColumn(['break_start_2', 'break_end_2']);
        });
    }
};
