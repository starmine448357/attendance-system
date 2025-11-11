<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧（月切替対応）
     */
    public function index(Request $request)
    {
        $monthParam = $request->query('month');
        $currentMonth = $monthParam ? Carbon::parse($monthParam) : Carbon::now('Asia/Tokyo');

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

        $days = [];
        $start = $currentMonth->copy()->startOfMonth();
        $end   = $currentMonth->copy()->endOfMonth();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = $d->copy();
        }

        return view('attendance.index', compact('attendances', 'currentMonth', 'days'));
    }

    /**
     * 打刻ページ
     */
    public function record()
    {
        $user = Auth::user();
        $today = now('Asia/Tokyo')->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->with('rests')
            ->first();

        if ($attendance) {
            $attendance->refresh(); // 最新状態を反映
        }

        $status = $attendance->status ?? '勤務外';

        return view('attendance.record', compact('attendance', 'status'));
    }

    /**
     * 打刻処理（休憩自動増加対応）
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = now('Asia/Tokyo')->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        switch ($request->input('type')) {

            // 出勤
            case 'start':
                if ($attendance) {
                    return redirect()->route('attendance.record');
                }

                Attendance::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'start_time' => now('Asia/Tokyo'),
                    'status' => '出勤中',
                ]);
                break;

            // 休憩開始（新しい休憩を自動で追加）
            case 'break_start':
                if (!$attendance) return redirect()->route('attendance.record');

                $attendance->status = '休憩中';
                $attendance->save();

                $lastRest = $attendance->rests()->latest()->first();

                if (!$lastRest || $lastRest->break_end !== null) {
                    $attendance->rests()->create([
                        'break_start' => now('Asia/Tokyo'),
                    ]);
                } else {
                    $lastRest->update(['break_start' => now('Asia/Tokyo')]);
                }
                break;

            // 休憩終了（最後の未完了休憩を閉じる）
            case 'break_end':
                if (!$attendance) return redirect()->route('attendance.record');

                $lastRest = $attendance->rests()->latest()->first();
                if ($lastRest && !$lastRest->break_end) {
                    $lastRest->update(['break_end' => now('Asia/Tokyo')]);
                }

                $totalBreak = $attendance->rests->sum(function ($r) {
                    if ($r->break_start && $r->break_end) {
                        return Carbon::parse($r->break_start)
                            ->diffInMinutes(Carbon::parse($r->break_end));
                    }
                    return 0;
                });

                $attendance->update([
                    'break_duration' => $totalBreak,
                    'status' => '出勤中',
                ]);
                break;

            // 退勤
            case 'end':
                if (!$attendance) return redirect()->route('attendance.record');

                $attendance->end_time = now('Asia/Tokyo');

                if ($attendance->start_time && $attendance->end_time) {
                    $start = Carbon::parse($attendance->start_time);
                    $end   = Carbon::parse($attendance->end_time);
                    $totalMinutes = max($start->diffInMinutes($end, false), 0);
                    $breakMinutes = max($attendance->break_duration ?? 0, 0);
                    $attendance->total_duration = max($totalMinutes - $breakMinutes, 0);
                }

                $attendance->status = '退勤済';
                $attendance->save();
                break;
        }

        return redirect()->route('attendance.record');
    }

    /**
     * 勤怠詳細（休憩全件・申請状態含む）
     */
    public function show($value)
    {
        // 数字ならID、文字列なら日付と判定
        if (ctype_digit($value)) {
            $attendance = Attendance::with(['rests', 'requests', 'user'])->find($value);
        } else {
            $attendance = Attendance::where('user_id', Auth::id())
                ->whereDate('date', Carbon::parse($value))
                ->with(['rests', 'requests', 'user'])
                ->first();
        }

        $pendingRequest = null;
        $approvedRequest = null;

        if ($attendance) {
            // 承認待ち申請
            $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            // 承認済み申請
            $approvedRequest = AttendanceRequest::where('attendance_id', $attendance->id)
                ->where('status', 'approved')
                ->latest()
                ->first();
        }

        return view('attendance.show', compact(
            'attendance',
            'pendingRequest',
            'approvedRequest'
        ));
    }
}
