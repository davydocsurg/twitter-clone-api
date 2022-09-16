<?php

namespace App\Http\Controllers\Tweet;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Tweet;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CrudController extends Controller
{
    /**
     * Create a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
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

        $this->store($request, $tweet);

        return response([
            'status' => true,
            'message' => 'Tweet Created',
            'data' => $tweet,
        ], 201);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($request, $tweet)
    {
        $tweet->user_id = Auth::user()->id;
        $tweet->tweet_text = $request->tweet_text;
        $tweet->slug = Str::slug(time() . '-' . substr($request->tweet_text, 0, 3));

        if ($request->tweet_photo) {
            $validateTweetPhoto = $this->tweet_photo_rules($request);

            // Run validation
            if ($validateTweetPhoto->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validateTweetPhoto->errors(),
                    'status' => 400,
                ]);
            } else {
                $tweetPhoto = $request->tweet_photo;

                $path = $tweetPhoto->store('tweets/photos', 'public');

                Image::create([
                    'url' => $path,
                    'imageable_id' => $tweet->slug,
                    'imageable_type' => 'App\Models\Tweet',
                ]);
            }

        }

        if ($request->tweet_video) {
            $validateTweetVideo = $this->tweet_video_rules($request);

            // Run validation
            if ($validateTweetVideo->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validateTweetVideo->errors(),
                    'status' => 400,
                ]);
            } else {
                $tweetVideo = $request->tweet_video;

                $path = $tweetVideo->store('tweets/videos', 'public');

                Video::create([
                    'url' => $path,
                    'videoable_id' => $tweet->slug,
                    'videoable_type' => 'App\Models\Tweet',
                ]);
            }
        }

        $tweet->save();
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
            'tweet_photo' => 'required|mimes:jpg,jpeg,png,bmp|max:4000',
        ]);
    }

    /**
     * Get a validator for an incoming request.
     *
     * @param  request  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function tweet_video_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'tweet_video' => 'required|mimes:mp4|max:10000',
        ]);
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
        if ($tweet->tweet_photo) {
            unlink(public_path('tweets/photos/' . $tweet->tweet_photo));
        }

        $tweet->delete();

        return response([
            'status' => true,
            'message' => 'Tweet Deleted',
        ], 200);

    }
}
