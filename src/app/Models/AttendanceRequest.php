<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'break_start_2',     // ← 追加
        'break_end_2',       // ← 追加
        'note',
        'status',
        'extra_rests_json',  // ← 追加
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
