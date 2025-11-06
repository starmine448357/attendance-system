<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class RequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time'          => ['nullable', 'date_format:H:i'],
            'end_time'            => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'rests.*.break_start' => ['nullable', 'date_format:H:i'],
            'rests.*.break_end'   => ['nullable', 'date_format:H:i'],
            'break_start'         => ['nullable', 'date_format:H:i'], // ← テスト用に追加
            'break_end'           => ['nullable', 'date_format:H:i'], // ← テスト用に追加
            'note'                => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end   = $this->input('end_time');

            $rests = $this->input('rests', []);
            if (empty($rests) && ($this->filled('break_start') || $this->filled('break_end'))) {
                $rests = [
                    ['break_start' => $this->input('break_start'), 'break_end' => $this->input('break_end')],
                ];
            }

            $startC = $start ? Carbon::createFromFormat('H:i', $start) : null;
            $endC   = $end ? Carbon::createFromFormat('H:i', $end) : null;

            // ✅ 出勤・退勤の両方が入力されていない場合
            if (empty($start) || empty($end)) {
                $validator->errors()->add('start_time', '出勤・退勤の両方を入力してください');
            }

            // ✅ 出勤 > 退勤 の場合
            if ($startC && $endC && $startC->gt($endC)) {
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // ===== 休憩時間チェック =====
            foreach ($rests as $index => $rest) {
                $bStart = $rest['break_start'] ?? null;
                $bEnd   = $rest['break_end'] ?? null;

                if (($bStart && !$bEnd) || (!$bStart && $bEnd)) {
                    $validator->errors()->add("rests.$index.break_start", '休憩時間を正しく入力してください');
                    continue;
                }

                $bStartC = $bStart ? Carbon::createFromFormat('H:i', $bStart) : null;
                $bEndC   = $bEnd ? Carbon::createFromFormat('H:i', $bEnd) : null;

                // 休憩開始が出勤前 or 退勤後
                if (($startC && $bStartC && $bStartC->lt($startC)) ||
                    ($endC && $bStartC && $bStartC->gt($endC))
                ) {
                    $validator->errors()->add("rests.$index.break_start", '休憩時間が不適切な値です');
                    continue;
                }

                // 休憩終了が退勤後
                if ($endC && $bEndC && $bEndC->gt($endC)) {
                    $validator->errors()->add("rests.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です');
                    continue;
                }

                // 休憩開始 > 終了
                if ($bStartC && $bEndC && $bStartC->gt($bEndC)) {
                    $validator->errors()->add("rests.$index.break_end", '休憩時間が不適切な値です');
                    continue;
                }
            }
        });
    }
    
    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
            'note.string'   => '備考は文字列で入力してください',
            'end_time.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
        ];
    }
}
