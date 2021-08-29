<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class TweetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tweets = Tweet::with('tweep', 'replies')->latest()->get();

        if ($tweets->count() > 0) {
            return $tweets;
        }
        return 'No tweets found';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
        $tweet->slug = Str::slug(time() . '-' . substr($request->tweet_text, 0, 3));

        // if ($request->hasFile('tweet_photo')) {
        if ($request->tweet_photo) {
            $uploadPhoto = time() . '.' . explode('/', explode(':', substr($request->tweet_photo, 0, strpos($request->tweet_photo, ';')))[1])[1];
            // dd($uploadPhoto);
            $validateTweetPhoto = $this->tweet_photo_rules($request);

            // Run validation
            if ($validateTweetPhoto->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validateTweetPhoto->errors(),
                    'status' => 400,
                ]);
            }

            // $uploadPhoto = basename($request->tweet_photo);

            // $madeImg = Image::make($request->tweet_photo);
            // Storage::put('/pubic/tweets/photos' . $uploadPhoto);
            // $madeImg->save($storeImg);
            Image::make($request->tweet_photo)->save('tweets/photos/' . $uploadPhoto);

            $request->merge(['tweet_photo' => $uploadPhoto]);

            // $uploadPhoto = Storage::put('/public/tweets/photos', $request->tweet_photo);

            // $tweet->tweet_photo = ('public/tweets/photos/') . $uploadPhoto;
            $tweet->tweet_photo = $uploadPhoto;
            // $uploadPhoto = Storage::put('/public/tweets/photos', $request->tweet_photo);

            // $tweetPhoto = basename($uploadPhoto);

            // $tweet->tweet_photo = $tweetPhoto;

            // if (!$uploadPhoto) {
            //     Storage::delete('/public/tweets/photos' . $uploadPhoto);

            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Oops! Something went wrong. Try Again!',
            //         'status' => 400,
            //     ]);
            // }
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
            'tweet_text' => 'required|string|max:250',
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
            'tweet_photo' => 'required',
            // 'tweet_photo' => 'required|file|max:5120',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showTweet(Tweet $tweet)
    {
        // $tweet = Tweet::findOrFail($tweet);
        if ($tweet->has('replies')) {
            // $tweet = Tweet::with('tweep', 'replies')->get();
            return $tweet->replies;
            // return $tweet;

        }

        return $tweet;

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tweet $tweet)
    {
        // dd($tweet->tweet_photo);
        try {
            if ($tweet->tweet_photo) {
                unlink(public_path('/storage/tweets/photos/' . $tweet->tweet_photo));
                // Storage::delete('/public/tweets/photos' . $tweet->tweet_photo);
            }

            // $tweet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tweet Deleted',
                'status' => 200,
                'tweet_photo' => $tweet->tweet_photo,
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
}
