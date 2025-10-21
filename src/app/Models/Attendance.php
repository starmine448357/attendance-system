<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'break_start' => 'datetime:H:i:s',
        'break_end' => 'datetime:H:i:s',
    ];

    /** ユーザーとのリレーション */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** 休憩時間（フォーマット済み） */
    public function getBreakDurationAttribute()
    {
        if ($this->break_start && $this->break_end) {
            return $this->break_end->diff($this->break_start)->format('%H:%I');
        }
        return '0:00';
    }

    /** 総勤務時間（休憩時間を引いた結果） */
    public function getTotalDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $total = $this->end_time->diffInMinutes($this->start_time);

            if ($this->break_start && $this->break_end) {
                $total -= $this->break_end->diffInMinutes($this->break_start);
            }

            $hours = intdiv($total, 60);
            $minutes = $total % 60;

            return sprintf('%d:%02d', $hours, $minutes);
        }

        return '0:00';
    }
}
