<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // スタッフユーザーとの紐づけ
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 勤務日（1日1レコード）
            $table->date('date');

            // 勤怠時刻（Laravelコントローラと対応）
            $table->time('start_time')->nullable();   // 出勤時刻
            $table->time('break_start')->nullable();  // 休憩開始
            $table->time('break_end')->nullable();    // 休憩終了
            $table->time('end_time')->nullable();     // 退勤時刻

            // ステータス（勤務外／出勤中／休憩中／退勤済）を日本語で保存
            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
