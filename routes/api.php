<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReplyController;
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
Route::post('signin', [AuthController::class, 'login'])->name('login');

// Route::get('all/tweets', [TweetController::class, 'allTweets']);
Route::get('tweets', [TweetController::class, 'index']);
// protected routes
// Route::group(['middleware' => ['auth:api']], function () {
Route::middleware('auth:api')->group(function () {
    // Route::apiResources(['tweets' => TweetController::class]);
    // tweet routes

    Route::post('tweet/create', [TweetController::class, 'createTweet']);
    Route::get('tweets/{tweet}/show', [TweetController::class, 'showTweet']);
    Route::post('tweets/{tweet}/destroy', [TweetController::class, 'destroy']);

    // reply routes
    Route::get('replies', [ReplyController::class, 'index']);
    Route::post('reply/{tweet}', [ReplyController::class, 'replyTweet']);
    Route::get('reply/{id}/show', [ReplyController::class, 'show']);
    Route::post('reply/{id}/destroy', [ReplyController::class, 'destroy']);

    // logout
    Route::post('signout', [AuthController::class, 'logout']);

    // authUser
    Route::get('authUser', [AuthController::class, 'getAuthUser']);

    // profile
    Route::get('/{handle}', [ProfileController::class, 'profile']);
    Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('/update-profile-picture', [ProfileController::class, 'updateProfilePicture']);
    Route::post('/update-cover-picture', [ProfileController::class, 'updateCoverPhoto']);
    Route::post('/update-password', [ProfileController::class, 'updatePassword']);

    // fetch authuser tweets
    Route::get('/authUserTweets', [ProfileController::class, 'authUserTweets']);

});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
