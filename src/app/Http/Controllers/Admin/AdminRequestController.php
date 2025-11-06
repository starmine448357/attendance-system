<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class AdminRequestController extends Controller
{
    /**
     * ======================================================
     * 申請一覧（承認待ち・承認済み）
     * ======================================================
     * - 管理者が全スタッフの申請を確認できる画面
     * - クエリパラメータ ?status=pending / approved により切り替え
     * - デフォルトは「承認待ち」
     */
    public function index(Request $request)
    {
        // 現在の表示ステータス（初期値は pending）
        $status = $request->input('status', 'pending');

        // 対象ステータスの申請を取得（ユーザー情報も一緒に）
        $requests = AttendanceRequest::with(['attendance.user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        // admin/request/index.blade.php にデータを渡す
        return view('admin.request.index', compact('requests', 'status'));
    }

    /**
     * ======================================================
     * 申請詳細（個別画面）
     * ======================================================
     * - 管理者が1件の勤怠修正申請を確認する画面
     * - 勤怠データや休憩データ、ユーザー情報を同時に取得
     * - Bladeで「$attendance」や「$request」を安全に使えるように渡す
     */
    public function show($id)
    {
        /**
         * ==============================
         * 対象の申請データを取得
         * ==============================
         */
        $request = AttendanceRequest::with(['attendance.user', 'attendance.rests'])->findOrFail($id);
        $attendance = $request->attendance ?? null;

        /**
         * ==============================
         * 各状態を判定（承認待ち or 承認済み）
         * ==============================
         * - pendingRequest: ステータスが「pending」
         * - approvedRequest: ステータスが「approved」
         */
        $pendingRequest = $request->status === 'pending' ? $request : null;
        $approvedRequest = $request->status === 'approved' ? $request : null;

        /**
         * ==============================
         * Bladeに渡す変数
         * ==============================
         * - $request : 申請情報
         * - $attendance : 勤怠情報
         * - $pendingRequest : 承認待ち時のみ値あり
         * - $approvedRequest : 承認済み時のみ値あり
         */
        return view('admin.request.show', compact(
            'request',
            'attendance',
            'pendingRequest',
            'approvedRequest'
        ));
    }

    /**
     * ======================================================
     * 承認処理（勤怠データ反映）
     * ======================================================
     * - 管理者が申請を承認したときに呼ばれる処理
     * - 勤怠テーブルに出退勤時間・休憩時間を反映
     * - 申請ステータスを「approved」に更新
     */
    public function approve($id)
    {
        // 申請を取得（勤怠データも一緒に）
        $request = AttendanceRequest::with('attendance')->findOrFail($id);
        $attendance = $request->attendance;

        // 万が一、対応する勤怠データがない場合は処理を中断
        if (!$attendance) {
            return redirect()->back();
        }

        /**
         * ==============================
         * 出退勤時間の更新
         * ==============================
         */
        $attendance->update([
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'status'     => '退勤済',
        ]);

        /**
         * ==============================
         * 休憩1・2件目を更新 or 作成
         * ==============================
         */
        $baseBreaks = [
            ['start' => $request->break_start,   'end' => $request->break_end],
            ['start' => $request->break_start_2, 'end' => $request->break_end_2],
        ];

        foreach ($baseBreaks as $i => $rest) {
            if ($rest['start'] || $rest['end']) {
                $attendance->rests()->updateOrCreate(
                    ['order' => $i + 1], // 1件目・2件目を区別
                    [
                        'break_start' => $rest['start'],
                        'break_end'   => $rest['end'],
                    ]
                );
            }
        }

        /**
         * ==============================
         * 休憩3件目以降（extra_rests_json）
         * ==============================
         * - 追加休憩はJSONで渡される形式を想定
         * - 配列展開して順番に登録
         */
        if (!empty($request->extra_rests_json)) {
            $extraRests = json_decode($request->extra_rests_json, true);
            $order = 3; // 3件目以降の連番スタート

            foreach ($extraRests as $rest) {
                if (!empty($rest['break_start']) || !empty($rest['break_end'])) {
                    $attendance->rests()->updateOrCreate(
                        ['order' => $order],
                        [
                            'break_start' => $rest['break_start'] ?? null,
                            'break_end'   => $rest['break_end'] ?? null,
                        ]
                    );
                    $order++;
                }
            }
        }

        /**
         * ==============================
         * ステータス更新（申請を承認済みに）
         * ==============================
         */
        $request->update(['status' => 'approved']);

        // 詳細画面にリダイレクト（最新データを再表示）
        return redirect()->route('admin.request.show', $id);
    }
}
