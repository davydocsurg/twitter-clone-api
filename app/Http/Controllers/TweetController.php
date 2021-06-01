<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TweetController extends Controller
{
    public function createTweet(Request $request)
    {
        $validateTweet = $this->tweet_rules($request);

        // Run validation
        if ($validateTweet->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validateTweet->errors(),
                'status' => 400,
            ]);
        }

        $tweet = new Tweet();

        $tweet->user_id = Auth::user()->id;
        $tweet->tweet_text = $request->tweet_text;
        $tweet->slug = Str::slug($request->tweet_text);

        if ($request->hasFile('tweet_photo')) {
            $validateTweetPhoto = $this->tweet_photo_rules($request);

            // Run validation
            if ($validateTweetPhoto->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validateTweetPhoto->errors(),
                    'status' => 400,
                ]);
            }

            $uploadPhoto = Storage::put('/public/tweets/photos', $request->tweet_photo);

            $tweetPhoto = basename($uploadPhoto);

            $tweet->tweet_photo = $tweetPhoto;

            if (!$uploadPhoto) {
                Storage::delete('/public/tweets/photos' . $tweetPhoto);

                return response()->json([
                    'success' => false,
                    'message' => 'Oops! Something went wrong. Try Again!',
                    'status' => 400,
                ]);
            }
        }

        // try tweet save or catch error(s)
        try {
            $tweet->save();

            return response()->json([
                'success' => true,
                'message' => 'Tweet Created',
                'status' => 200,
                'tweet' => $tweet,
                // 'access_token' => $token,
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Oops! Something went wrong. Try Again!',
            ]);
        }
    }

    /**
     * Tweet Validation Rules
     * @return object The validator object
     */
    public function tweet_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'tweet_text' => 'required|string',
        ]);
    }

    /**
     * Get a validator for an incoming request.
     *
     * @param  request  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function tweet_photo_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'tweet_photo' => 'required|file|max:5120',
        ]);
    }
}
