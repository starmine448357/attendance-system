<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * 申請一覧の表示
     */
    public function index()
    {
        return view('request.index');
    }

    /**
     * 勤怠修正申請の送信処理
     */
    public function store(Request $request)
    {
        // ここでフォームから送られた内容を受け取る
        // まだDBがないので、ひとまず確認用にdd()で出す
        // 例: dd($request->all());

        // 実装後は保存処理 → リダイレクト予定
        return redirect()->route('request.index')->with('message', '申請を送信しました');
    }
}
