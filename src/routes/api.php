<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//https://eujobbd.com/api/candidates/inactivate-old?token=default_cron_token_123
Route::any('/candidates/inactivate-old', [\App\Http\Controllers\ApiController::class, 'inactivateCandidates']);
