<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class AdminRequestController extends Controller
{
    /**
     * 申請一覧（承認待ち・承認済み）
     */
    public function index(Request $request)
    {
        // デフォルトは「承認待ち」
        $status = $request->input('status', 'pending');

        // 指定されたステータスの申請のみ取得
        $requests = AttendanceRequest::with(['attendance.user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ $status をビューに渡す！
        return view('admin.request.index', compact('requests', 'status'));
    }

    /**
     * 申請詳細
     */
    public function show($id)
    {
        $request = AttendanceRequest::with(['attendance.user', 'attendance.rests'])->findOrFail($id);
        return view('admin.request.show', compact('request'));
    }

    /**
     * 承認処理
     */
    public function approve($id)
    {
        $request = AttendanceRequest::findOrFail($id);
        $request->update(['status' => 'approved']);

        return redirect()->route('admin.request.show', $id)
            ->with('status', '申請を承認しました。');
    }
}
