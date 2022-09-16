<?php

namespace App\Http\Controllers\Tweet;

use App\Http\Controllers\Controller;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TweetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tweets = Tweet::latest()->get();

        if ($tweets->count() > 0) {
            // dd($tweets->images());
            return $tweets;
        }
        return 'No tweets found';

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showTweet($tweet)
    {
        // $tweetDeatails = Tweet::findOrFail($tweet);
        // dd($tweet);
        $data = Tweet::where('slug', $tweet)->first();
        if ($data->has('likes', 'replies')) {
            // $tweet = Tweet::with('tweep', 'replies')->get();
            // return $tweet->replies;
            return $data->with('likes', 'replies')->first();

        }
        return $data;

    }

    /**
     * Reply the specified tweet.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function replyTweet(Tweet $tweet)
    {
        return $tweet;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function authUserTweets()
    {

        // $authUser = Auth::user();
        $authUserTweets = auth()->user()->tweets()->get();

        try {
            // return $authUser->with($authUserTweets)->get();

            return response()->json([
                'success' => true,
                'message' => 'User Profile',
                'status' => 200,
                // 'authUser' => $authUser,
                'authUserTweets' => $authUserTweets,
            ]);
        } catch (\Throwable$th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Oops! Something went wrong. Try Again!',
            ]);
        }
    }
}
