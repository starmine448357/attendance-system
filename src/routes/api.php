<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file defines your API routes. All routes here are automatically
| assigned the "api" middleware group.
|
*/

Route::get('/test', function () {
    return response()->json(['message' => 'API動作確認OK']);
});
