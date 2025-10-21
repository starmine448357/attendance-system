<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Auth\Events\Registered; // ← 追加

class RegisteredUserController extends Controller
{
    public function store(Request $request, CreateNewUser $creator)
    {
        // ユーザー作成
        $user = $creator->create($request->all());

        // 🔽 これが無いとメールは送られない（必須）
        event(new Registered($user));

        // （必要なら）認証前ユーザーの保持
        session()->put('unauthenticated_user', $user);

        // 認証メール送信後の案内画面へ
        return redirect()->route('verification.notice');
    }
}
