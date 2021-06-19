<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $replies = Reply::with('tweet', 'tweep')->latest()->get();
        return $replies;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function replyTweet(Request $request, Tweet $tweet)
    {
        $validateReply = $this->reply_rules($request);

        // Run validation
        if ($validateReply->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validateReply->errors(),
                'status' => 400,
            ]);
        }

        $reply = new Reply();

        $reply->user_id = Auth::user()->id;
        $reply->tweet_id = $tweet->id;
        $reply->reply_text = $request->reply_text;

        if ($request->hasFile('reply_photo')) {
            $validateReplyPhoto = $this->reply_photo_rules($request);

            // Run validation
            if ($validateReplyPhoto->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validateReplyPhoto->errors(),
                    'status' => 400,
                ]);
            }

            $uploadPhoto = Storage::put('/public/replies/photos', $request->reply_photo);

            $replyPhoto = basename($uploadPhoto);

            $reply->reply_photo = $replyPhoto;

            if (!$uploadPhoto) {
                Storage::delete('/public/replies/photos' . $replyPhoto);

                return response()->json([
                    'success' => false,
                    'message' => 'Oops! Something went wrong. Try Again!',
                    'status' => 400,
                ]);
            }
        }

        // try tweet save or catch error(s)
        try {
            $reply->save();

            return response()->json([
                'success' => true,
                'message' => 'Reply Created',
                'status' => 200,
                'reply' => $reply,
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
    public function reply_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'reply_text' => 'required|string',
        ]);
    }

/**
 * Get a validator for an incoming request.
 *
 * @param  request  $data
 * @return \Illuminate\Contracts\Validation\Validator
 */
    public function reply_photo_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'reply_photo' => 'required|file|max:5120',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reply = Reply::findOrFail($id);

        // try reply delete or catch error(s)
        try {
            return $reply->with('tweet', 'tweep')->get();

            return response()->json([
                'success' => true,
                'message' => 'Reply Deleted',
                'status' => 200,
                'reply' => $reply,
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
    public function destroy($id)
    {
        $reply = Reply::findOrFail($id);

        // try reply delete or catch error(s)
        try {
            if ($reply->reply_photo > 0) {
                unlink(public_path('/storage/replies/photos/' . $reply->reply_photo));
                // Storage::delete('/public/replies/photos/' . $reply->reply_photo);
            }
            $reply->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reply Deleted',
                'status' => 200,
                'reply' => $reply,
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
}
