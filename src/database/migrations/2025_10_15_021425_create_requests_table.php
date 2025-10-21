<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();

            // 申請者（一般ユーザー）
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 修正対象の勤怠
            $table->foreignId('attendance_id')
                ->constrained()
                ->cascadeOnDelete();

            // 申請された修正後の値
            $table->time('start_time')->nullable();  // 出勤
            $table->time('end_time')->nullable();    // 退勤
            $table->json('rests')->nullable();       // 休憩（[{start,end},{…}]）
            $table->text('note')->nullable();        // 備考

            // 申請の状態
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
