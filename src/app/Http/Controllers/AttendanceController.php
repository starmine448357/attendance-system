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

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'date'    => $today,
        ]);

        switch ($request->input('type')) {

            /**
             * 出勤
             */
            case 'start':
                $attendance->start_time = now('Asia/Tokyo');
                $attendance->status = '出勤中';
                $attendance->save();
                break;

            /**
                 * 休憩開始（新しい休憩を自動で追加）
                 */
            case 'break_start':
                $attendance->status = '休憩中';
                $attendance->save();

                $lastRest = $attendance->rests()->latest()->first();

                if (!$lastRest || $lastRest->break_end !== null) {
                    // 直前の休憩が終了済みなら新規追加
                    $attendance->rests()->create([
                        'break_start' => now('Asia/Tokyo'),
                    ]);
                } else {
                    // break_end が未入力のまま再度押された場合の安全対策
                    $lastRest->update(['break_start' => now('Asia/Tokyo')]);
                }
                break;

            /**
                 * 休憩終了（最後の未完了休憩を閉じる）
                 */
            case 'break_end':
                $lastRest = $attendance->rests()->latest()->first();
                if ($lastRest && !$lastRest->break_end) {
                    $lastRest->update(['break_end' => now('Asia/Tokyo')]);
                }

                // 総休憩時間を再計算
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

            /**
                 * 退勤
                 */
            case 'end':
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
     * 勤怠詳細
     */
    public function show($value)
    {
        if (ctype_digit($value)) {
            $attendance = Attendance::with(['rests', 'user'])->find($value);
        } else {
            $attendance = Attendance::where('user_id', Auth::id())
                ->whereDate('date', \Carbon\Carbon::parse($value))
                ->with(['rests', 'user'])
                ->first();
        }

        $pendingRequest = null;
        $approvedRequest = null; // ← これ追加！

        if ($attendance) {
            // 承認待ち申請
            $pendingRequest = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            // 承認済み申請
            $approvedRequest = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)
                ->where('status', 'approved')
                ->latest()
                ->first();
        }

        return view('attendance.show', compact(
            'attendance',
            'pendingRequest',
            'approvedRequest' // ← これをビューに渡す
        ));
    }
}
