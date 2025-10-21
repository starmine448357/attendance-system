<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as AttendanceRequest;

class RequestController extends Controller
{
    /**
     * 申請一覧の表示
     */
    public function index()
    {
        $requests = AttendanceRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('request.index', compact('requests'));
    }

    /**
     * 勤怠修正申請の送信処理
     */
    public function store(Request $request)
    {
        $attendanceId = $request->attendance_id;
        $start = $request->start_time;
        $end   = $request->end_time;
        $notes = $request->note;

        $rests = [];
        if ($request->has('rest_start')) {
            foreach ($request->rest_start as $i => $rs) {
                $re = $request->rest_end[$i] ?? null;
                if ($rs || $re) {
                    $rests[] = ['start' => $rs, 'end' => $re];
                }
            }
        }

        AttendanceRequest::create([
            'user_id'       => auth()->id(),
            'attendance_id' => $attendanceId,
            'start_time'    => $start,
            'end_time'      => $end,
            'rests'         => $rests,
            'note'          => $notes,
            'status'        => 'pending',
        ]);

        return redirect()
            ->route('request.index')
            ->with('message', '申請を送信しました');
    }
}
