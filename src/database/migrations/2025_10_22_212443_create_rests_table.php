<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->time('break_start')->nullable(); // ← startより具体的で既存命名に合わせる
            $table->time('break_end')->nullable();   // ← end → break_end に統一
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rests');
    }
};
