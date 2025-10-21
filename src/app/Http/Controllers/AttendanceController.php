<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧
     */
    public function index(Request $request)
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance.index', compact('attendances'));
    }

    /**
     * 打刻ページ（今日の勤怠状態を判定して表示）
     */
    public function record()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 今日の勤怠レコードを取得（なければ null）
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // ===============================
        // ステータス判定
        // ===============================
        if (!$attendance) {
            // 初回ログインなど、勤怠レコードがまだない場合
            $status = '勤務外';
            $attendance = null; // ← 仮レコードは作らない
        } else {
            // 既存レコードあり → DBのstatusを参照（なければ勤務外）
            $status = $attendance->status ?? '勤務外';
        }

        return view('attendance.record', compact('attendance', 'status'));
    }

    /**
     * 打刻処理
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 今日の勤怠レコードを取得 or 新規作成
        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'date' => $today,
        ]);

        // ===============================
        // ボタン種別に応じて処理分岐
        // ===============================
        switch ($request->input('type')) {
            case 'start':
                // 出勤
                $attendance->start_time = now();
                $attendance->status = '出勤中';
                break;

            case 'break_start':
                // 休憩開始
                $attendance->break_start = now();
                $attendance->status = '休憩中';
                break;

            case 'break_end':
                // 休憩終了 → 出勤中へ戻る
                $attendance->break_end = now();
                $attendance->status = '出勤中';
                break;

            case 'end':
                // 退勤
                $attendance->end_time = now();
                $attendance->status = '退勤済';
                break;
        }

        $attendance->save();

        return redirect()->route('attendance.record');
    }
}
