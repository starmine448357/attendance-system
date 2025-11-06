<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;

class CreateNewUser
{
    /**
     * 新規ユーザー登録処理
     */
    public function create(array $input)
    {
        // RegisterRequestのルール＆メッセージを適用
        $request = new RegisterRequest();
        $validator = Validator::make($input, $request->rules(), $request->messages());
        $validator->validate();

        // 登録処理
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
