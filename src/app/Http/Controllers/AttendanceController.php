<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * 打刻画面の表示
     */
    public function record()
    {
        // まだDB接続前なので仮データを渡す
        $attendance = null;
        return view('attendance.record', compact('attendance'));
    }

    /**
     * 勤怠一覧の表示
     */
    public function index()
    {
        return view('attendance.index');
    }

    /**
     * 勤怠詳細の表示
     */
    public function show($id)
    {
        return view('attendance.show', compact('id'));
    }
}
