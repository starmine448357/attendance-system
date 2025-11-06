<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧
     */
    public function index()
    {
        // users テーブル = 従業員（スタッフ）
        $staffs = User::select('id', 'name', 'email', 'created_at')->get();

        return view('admin.staff.index', compact('staffs'));
    }
}
