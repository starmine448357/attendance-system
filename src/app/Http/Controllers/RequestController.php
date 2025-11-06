<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\RequestStoreRequest;

class RequestController extends Controller
{
    /**
     * 修正申請一覧（スタッフ用）
     */
    public function index()
    {
        $status = request('status');

        $requests = AttendanceRequest::where('user_id', Auth::id())
            ->when($status === 'approved', fn($q) => $q->where('status', 'approved'))
            ->when($status === 'pending' || !$status, fn($q) => $q->where('status', 'pending'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('request.index', compact('requests'));
    }

    /**
     * 修正申請の保存処理
     */
    public function store(RequestStoreRequest $request)
    {
        $attendanceId = $request->attendance_id;

        if (!$attendanceId) {
            $date = $request->date;

            // 勤怠が存在しなければ自動作成
            $attendance = Attendance::firstOrCreate(
                ['user_id' => Auth::id(), 'date' => $date],
                ['status' => '勤務外']
            );

            $attendanceId = $attendance->id;
        }

        // 🔽 休憩時間を配列から取得（最大2件＋3件目以降をJSON化）
        $rests = $request->input('rests', []);

        $breakStart1 = $rests[0]['break_start'] ?? null;
        $breakEnd1   = $rests[0]['break_end'] ?? null;
        $breakStart2 = $rests[1]['break_start'] ?? null;
        $breakEnd2   = $rests[1]['break_end'] ?? null;

        // ✅ 3件目以降をJSONとして格納
        $extraRests = [];
        if (count($rests) > 2) {
            $extraRests = array_slice($rests, 2);
        }

        // 勤怠修正申請を登録
        AttendanceRequest::create([
            'attendance_id'    => $attendanceId,
            'user_id'          => Auth::id(),
            'start_time'       => $request->start_time,
            'end_time'         => $request->end_time,
            'break_start'      => $breakStart1,
            'break_end'        => $breakEnd1,
            'break_start_2'    => $breakStart2,
            'break_end_2'      => $breakEnd2,
            'note'             => $request->note,
            'status'           => 'pending',
            'extra_rests_json' => !empty($extraRests) ? json_encode($extraRests) : null,
        ]);

        return redirect()->route('request.index', ['status' => 'pending']);
    }
}
