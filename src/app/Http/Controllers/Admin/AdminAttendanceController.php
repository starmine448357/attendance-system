<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

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
    // 数字なら勤怠ID、日付形式なら指定日のデータ
    if (ctype_digit($value)) {
        $attendance = Attendance::with(['user', 'rests'])->find($value);
    } else {
        $attendance = Attendance::whereDate('date', Carbon::parse($value))
            ->with(['user', 'rests'])
            ->first();
    }

    // まだ出勤していない日（勤怠レコードが存在しない場合）
    if (!$attendance) {
        $attendance = new Attendance([
            'date' => Carbon::parse($value),
            'start_time' => null,
            'end_time' => null,
            'break_duration' => null,
            'total_duration' => null,
        ]);
        $attendance->rests = collect(); // 空の休憩リストをセット
    }

    return view('admin.attendance.show', compact('attendance'));
}
    /**
     * 勤怠更新（管理者による修正）
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'start_time' => $request->start_time ?: null,
            'end_time'   => $request->end_time ?: null,
            'note'       => $request->note ?: null,
        ]);

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
