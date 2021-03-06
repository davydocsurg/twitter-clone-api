<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    // public function profile(User $user)
    // {

    //     $authUser = Auth::user();
    //     $authUserTweets = auth()->user()->tweets()->get();

    //     try {
    //         // return $authUser->with($authUserTweets)->get();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User Profile',
    //             'status' => 200,
    //             'authUser' => $authUser,
    //             'authUserTweets' => $authUserTweets,
    //         ]);
    //     } catch (\Throwable $th) {
    //         Log::error($th);
    //         return response()->json([
    //             'success' => false,
    //             'status' => 500,
    //             'message' => 'Oops! Something went wrong. Try Again!',
    //         ]);
    //     }
    // }

    public function profile(User $user)
    {

        $user = User::findOrFail($user);
        // $user = User::with('tweets')->get();
        // if ($user->tweets->count() > 0) {
        //     return $user->tweets;
        // }
        // $userTweets = $user->with('tweets')->get();
        try {
            // return $user;

            return response()->json([
                'success' => true,
                'message' => 'User Profile',
                'status' => 200,
                'user' => $user,
                // 'userTweets' => $userTweets,
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

    public function updateProfile(Request $request)
    {
        $validateUserDetails = $this->update_profile_rules($request);

        // Run validation
        if ($validateUserDetails->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validateUserDetails->errors(),
                'status' => 400,
            ]);
        }

        $user = Auth::user();

        if ($request->first_name) {
            $request->validate([
                'first_name' => 'string|max:100',
            ]);
            $user->first_name = $request->first_name;
        }

        if ($request->last_name) {
            $request->validate([
                'last_name' => 'string|max:100',
            ]);
            $user->last_name = $request->last_name;
        }

        if ($request->email) {
            $request->validate([
                'email' => 'email|max:100|unique:users,email,' . $user->id,
            ]);
            $user->email = $request->email;
        }

        if ($request->handle) {
            $request->validate([
                'handle' => 'string|min:4|max:15|unique:users,handle,' . $user->id,
            ]);

            $user->handle = $request->handle;
        }

        if ($request->bio) {
            $request->validate([
                'bio' => 'string|min:4|max:455',
            ]);
            $user->bio = $request->bio;
        }

        if ($request->website) {
            $request->validate([
                'website' => 'url|min:4|max:255',
            ]);

            $user->website = $request->website;

        }

        if ($request->location) {
            $request->validate([
                'location' => 'string|min:4|max:255',
            ]);
            $user->location = $request->location;
        }

        try {
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile Updated',
                'status' => 200,
                'user' => $user,
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

    /**
     * Tweet Validation Rules
     * @return object The validator object
     */
    public function update_profile_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
        ]);
    }

    public function updateProfilePicture(Request $request)
    {
        // $validateProfilePicture = $this->profile_picture_rules($request);

        // // Run validation
        // if ($validateProfilePicture->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $validateProfilePicture->errors(),
        //         'status' => 400,
        //     ]);
        // }

        $user = Auth::user();
        $old = $user->profile_picture;

        // $uploadPhoto = Storage::put('/public/users/profile', $request->profile_picture);

        $uploadProfilePhoto = time() . '.' . explode('/', explode(':', substr($request->profile_picture, 0, strpos($request->profile_picture, ';')))[1])[1];
        // dd($uploadPhoto);
        // $validateProfilePhoto = $this->tweet_photo_rules($request);
        Image::make($request->profile_picture)->save('profile/photos/' . $uploadProfilePhoto);
        $request->merge(['profile_picture' => $uploadProfilePhoto]);

        // $profilePicture = basename($uploadProfilePhoto);

        $user->profile_picture = $uploadProfilePhoto;

        if (!$uploadProfilePhoto) {
            $uploadProfilePhoto != 'avatar.png' ? Storage::delete('/profile/photos/' . $uploadProfilePhoto) : null;

            return response()->json([
                'success' => false,
                'message' => 'Oops! Something went wrong. Try Again!',
                'status' => 400,
            ]);
        }

        try {
            $user->save();
            $old != 'avatar.png' ? unlink(public_path('/profile/photos/' . $old)) : null;

            return response()->json([
                'success' => true,
                'message' => 'Profile Updated',
                'status' => 200,
                'profile_picture' => $user->profile_picture,
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

    public function updateCoverPhoto(Request $request)
    {

        $user = Auth::user();
        $old = $user->cover_picture;

        $uploadCoverPhoto = time() . '.' . explode('/', explode(':', substr($request->cover_picture, 0, strpos($request->cover_picture, ';')))[1])[1];

        Image::make($request->cover_picture)->save('profile/photos/' . $uploadCoverPhoto);
        $request->merge(['cover_picture' => $uploadCoverPhoto]);

        // $profilePicture = basename($uploadCoverPhoto);

        $user->cover_picture = $uploadCoverPhoto;

        if (!$uploadCoverPhoto) {
            $uploadCoverPhoto != 'avatar.png' || 'hack.jpeg' ? Storage::delete('/profile/photos/' . $uploadCoverPhoto) : null;

            return response()->json([
                'success' => false,
                'message' => 'Oops! Something went wrong. Try Again!',
                'status' => 400,
            ]);
        }

        try {
            $user->save();
            $old != 'avatar.png' || 'hack.jpeg' ? unlink(public_path('/profile/photos/' . $old)) : null;

            return response()->json([
                'success' => true,
                'message' => 'Profile Updated',
                'status' => 200,
                'cover_picture' => $user->cover_picture,
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

    /**
     * Get a validator for an incoming request.
     *
     * @param  request  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function profile_picture_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'profile_picture' => 'required|file|max:5120',
        ]);
    }

    public function updatePassword(Request $request)
    {
        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return back()->with([
                'current_password_msg' => 'Your current password does not matches with the password you provided! Please try again.',
            ]);
            // return back();
        }
        if (strcmp($request->get('current_password'), $request->get('new_password')) == 0) {
            return back()->with([
                'new_password_msg' => 'New Password cannot be same as your current password! Please choose a different password.',
            ]);
            return back();
        }

        $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        $user->password = bcrypt($request->get('new_password'));
        $user->save();

        Auth::logout();
        session()->flash('password-success', 'Password Updated Successfully! Please Login.');

        return redirect()->route('login');
    }
}
