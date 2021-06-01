<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TweetController;
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

//  guest routes
Route::post('signup', [AuthController::class, 'signUp']);
Route::post('login', [AuthController::class, 'login'])->name('login');

// protected routes
// Route::group(['middleware' => ['auth:api']], function () {
Route::middleware('auth:api')->group(function () {
    Route::post('tweet/create', [TweetController::class, 'createTweet'])->name('create-tweet');
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});