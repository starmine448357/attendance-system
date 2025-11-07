<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\AdminUpdateAttendanceRequest;

class AdminAttendanceController extends Controller
{
    /**
     * 日次勤怠一覧
     */
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);

        $attendances = Attendance::with('user')
            ->whereDate('date', $currentDate)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.index', compact('attendances', 'currentDate'));
    }

    /**
     * 勤怠詳細（1件）
     */
    public function show($value)
    {
        // 日付かIDかを判定
        if (ctype_digit($value)) {
            $attendance = Attendance::with(['user', 'rests'])->find($value);
        } else {
            $date = Carbon::parse($value)->toDateString();

            // user_id を取得（クエリ or デフォルト）
            $userId = request()->query('user_id');
            if (!$userId) {
                $userId = User::orderBy('id')->value('id'); // 仮に1人目を対象
            }

            // 勤怠データ取得 or 自動作成
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $userId,
                    'date' => $date,
                ],
                [
                    'status' => '勤務外',
                    'start_time' => null,
                    'end_time' => null,
                    'break_duration' => 0,
                    'total_duration' => 0,
                ]
            );

            // 関連をロード
            $attendance->load(['user', 'rests']);
        }

        // ✅ rests を最低2件にしておく（休憩1・休憩2を常に表示）
        $rests = $attendance->rests ?? collect();
        $count = $rests->count();
        if ($count < 2) {
            for ($i = $count; $i < 2; $i++) {
                $rests->push([
                    'break_start' => null,
                    'break_end' => null,
                ]);
            }
        }
        $attendance->rests = $rests;

        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * 勤怠更新（管理者による修正）
     */

    public function update(AdminUpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validated();

        $attendance->update([
            'start_time' => $validated['start_time'] ?? null,
            'end_time'   => $validated['end_time'] ?? null,
            'note'       => $validated['note'] ?? null,
        ]);

        // 休憩テーブル更新
        $attendance->rests()->delete();

        if (!empty($validated['rests'])) {
            foreach ($validated['rests'] as $index => $restData) {
                if (!empty($restData['break_start']) || !empty($restData['break_end'])) {
                    $attendance->rests()->create([
                        'order' => $index + 1,
                        'break_start' => $restData['break_start'] ?? null,
                        'break_end'   => $restData['break_end'] ?? null,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('status', '勤怠情報を更新しました。');
    }

    /**
     * ✅ 月次勤怠一覧（スタッフ別）
     */
    public function monthly(Request $request, $user_id)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month . '-01');

        // 対象スタッフ
        $user = User::findOrFail($user_id);

        // 1日〜末日のリストを生成
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        $days = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $days->push($date->copy());
        }

        // 出勤データを日付キー化
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));

        return view('admin.attendance.monthly', compact(
            'user',
            'currentMonth',
            'days',
            'attendances'
        ));
    }

    /**
     * ✅ CSV出力処理
     */
    public function exportCsv($user_id, $month)
    {
        $user = User::findOrFail($user_id);
        $targetMonth = Carbon::parse($month . '-01');

        $records = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $targetMonth->month)
            ->whereYear('date', $targetMonth->year)
            ->orderBy('date', 'asc')
            ->get();

        $csvData = [];
        $csvData[] = ['日付', '出勤', '退勤', '休憩', '合計', '備考'];

        foreach ($records as $r) {
            $csvData[] = [
                $r->date->format('Y-m-d'),
                $r->start_time ? Carbon::parse($r->start_time)->format('H:i') : '',
                $r->end_time ? Carbon::parse($r->end_time)->format('H:i') : '',
                $r->break_duration ? floor($r->break_duration / 60) . ':' . sprintf('%02d', $r->break_duration % 60) : '',
                $r->total_duration ? floor($r->total_duration / 60) . ':' . sprintf('%02d', $r->total_duration % 60) : '',
                $r->note ?? '',
            ];
        }

        $filename = "attendance_{$user->id}_{$targetMonth->format('Y_m')}.csv";

        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
