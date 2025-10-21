<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'start_time',
        'end_time',
        'rests',
        'note',
        'status',
    ];

    protected $casts = [
        'rests' => 'array', // JSONを配列として扱う
    ];

    /**
     * 申請者（一般ユーザー）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 対象の勤怠
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
