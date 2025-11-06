<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Attendance
 *
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon|null $start_time
 * @property \Carbon\Carbon|null $end_time
 * @property int|null $break_duration
 * @property int|null $total_duration
 * @property string|null $status
 *
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Rest[] $rests
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\AttendanceRequest[] $requests
 */
class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'break_start',
        'break_end',
        'break_start_2',
        'break_end_2',
        'end_time',
        'break_duration',
        'total_duration',
        'status',
    ];

    protected $casts = [
        'date'          => 'date',
        'start_time'    => 'datetime:H:i:s',
        'end_time'      => 'datetime:H:i:s',
        'break_start'   => 'datetime:H:i:s',
        'break_end'     => 'datetime:H:i:s',
        'break_start_2' => 'datetime:H:i:s',
        'break_end_2'   => 'datetime:H:i:s',
    ];

    /** ユーザー（多対1） */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** 休憩（1対多） */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    /** 修正申請（1対多） */
    public function requests()
    {
        return $this->hasMany(\App\Models\AttendanceRequest::class);
    }

    /** ✅ 総休憩時間（DBに保存済みならそれを返す） */
    public function getBreakDurationAttribute($value)
    {
        return (int) $value;
    }

    /** ✅ 総勤務時間（出退勤時間と休憩時間から自動算出） */
    public function getTotalDurationAttribute($value)
    {
        // DBに保存済みなら優先
        if ($value !== null) {
            return (int) $value;
        }

        // 出勤 or 退勤がなければ 0
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        // 総勤務時間（分単位）
        $total = Carbon::parse($this->end_time)
            ->diffInMinutes(Carbon::parse($this->start_time));

        // 休憩①
        $break1 = 0;
        if ($this->break_start && $this->break_end) {
            $break1 = Carbon::parse($this->break_end)
                ->diffInMinutes(Carbon::parse($this->break_start));
        }

        // 休憩②
        $break2 = 0;
        if ($this->break_start_2 && $this->break_end_2) {
            $break2 = Carbon::parse($this->break_end_2)
                ->diffInMinutes(Carbon::parse($this->break_start_2));
        }

        // 総休憩時間合計
        $totalBreak = $break1 + $break2;

        // 総勤務時間 - 休憩時間
        return max($total - $totalBreak, 0);
    }
}
