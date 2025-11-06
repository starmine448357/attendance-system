<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    /**
     * 管理者ログインフォーム表示
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 認証試行
        if (!Auth::guard('admin')->attempt($credentials)) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません。',
            ])->withInput();
        }

        // 成功時：勤怠一覧ページへ
        return redirect()->route('admin.attendance.index');
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
