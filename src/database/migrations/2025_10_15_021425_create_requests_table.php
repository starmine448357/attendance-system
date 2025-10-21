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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 申請者
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade'); // 対象勤怠
            $table->text('content'); // 修正内容
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // 承認状態
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
