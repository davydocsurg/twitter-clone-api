<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function GetTweetLikes(Like $like)
    {
        $likes = Like::latest()->get();
        return $likes;
    }

    public function likeTweet(Request $request, Tweet $tweet)
    {
        $like = new Like();
        $like->user_id = Auth::user()->id;
        $like->tweet_id = $tweet->id;
        $like->tweet_slug = $tweet->slug;

        $like->like_count += 1;

        // $tweet->likes += 1;

        // try like save or catch error(s)
        try {
            $like->save();

            return response()->json([
                'success' => true,
                'message' => 'Tweet liked',
                'status' => 200,
                'like' => $like,
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

    public function unlikeTweet(Like $like, Tweet $tweet)
    {
        // $unlikeTweet = $tweet->tweep->like();
        // dd($unlikeTweet);
        $like = Like::findOrFail($tweet->id);
        // $liked = Like::where($like->tweet_id, $tweet->id);

        try {
            // $tweet->tweep->like()->delete();
            // $liked->delete();
            $like->like_count - 1;

            return response()->json([
                'success' => true,
                'message' => 'Tweet unliked',
                'status' => 200,
                'like' => $like,
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
