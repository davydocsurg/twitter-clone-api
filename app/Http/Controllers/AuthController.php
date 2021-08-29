<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signUp(Request $request)
    {

        // Get validation rules
        $validate = $this->registration_rules($request);

        // Run validation
        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
                'status' => 400,
            ]);
        }

        // dd('validation');
        $user = new User();

        if ($request->hasFile('profile_picture')) {
            // $old=$user->profile_picture;
            $validateProfilePicture = $this->profile_picture_rules($request);

            // Run validation
            if ($validateProfilePicture->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validateProfilePicture->errors(),
                    'status' => 400,
                ]);
            }

            $uploadPhoto = Storage::put('/public/users/profile', $request->profile_picture);

            $profilePicture = basename($uploadPhoto);

            $user->profile_picture = $profilePicture;

            if (!$uploadPhoto) {
                // Storage::delete('/public/users/profile' . $profilePicture);
                $profilePicture != 'avatar.png' ? Storage::delete('/public/users/profile/' . $profilePicture) : null;

                return response()->json([
                    'success' => false,
                    'message' => 'Oops! Something went wrong. Try Again!',
                    'status' => 400,
                ]);
            }

        }

        // $user = User::create([
        $user->first_name = $request->first_name; //$fields['first_name'],
        $user->last_name = $request->last_name; //$fields['last_name'],
        $user->email = $request->email; //$fields['email'],
        $user->handle = $request->handle; //$fields['handle'],
        $user->password = Hash::make(strtolower($request->password)); //bcrypt($fields['password']),
        // ]);
        $token = $user->createToken('authToken')->accessToken;

        // Try user save or catch error if any
        try {

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Signup Successful',
                'status' => 200,
                'user' => $user,
                'access_token' => $token,
            ]);
        } catch (\Throwable $th) {
            $profilePicture != 'avatar.png' ? Storage::delete('/public/users/profile/' . $profilePicture) : null;
            Log::error($th);
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Oops! Something went wrong. Try Again!',
            ]);
        }

        // return response($response, 201);
    }

    /**
     * Signup Validation Rules
     * @return object The validator object
     */
    public function registration_rules(Request $request)
    {
        // Make and return validation rules
        return Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'handle' => 'required|string|min:4|max:15|unique:users,handle',
            'password' => 'required|min:8|max:30|string|confirmed',
        ]);
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

    public function login(Request $request)
    {
        // $fields =
        $validate = $this->login_rules($request);

        // Run validation
        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
                'status' => 400,
            ]);
        }

        $login = $request->login;
        $password = $request->password;

        $attempt = false;

        // Attempt login with email
        $attempt = Auth::attempt(['email' => $login, 'password' => $password], $request->remember_me);

        // Attempt login with handle
        $attempt = $attempt ? $attempt : Auth::attempt(['handle' => $login, 'password' => $password], $request->remember_me);

        // create token for user
        $token = Auth::user()->createToken('authToken')->accessToken;

        // Return response
        if ($attempt) {

            return response()->json([
                'success' => true,
                'message' => 'Login Successful',
                'status' => 200,
                'user' => $login,
                'access_token' => $token,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Credentials',
                'status' => 400,
            ]);
        }

    }

    /**
     * Login Validation Rules
     * @return object The validator object
     */
    public function login_rules(Request $request)
    {
        // Custom login field message
        $message = [
            'login.required' => 'This field is required.',
        ];

        // Make and return validation rules
        return Validator::make($request->all(), [
            'login' => 'required',
            'password' => 'required',
        ], $message);
    }

    public function logout(Request $request)
    {
        try {

            Auth::user()->token()->delete();

            // Auth::user()->tokens->each(function ($token, $key) {
            //     $token->delete();
            // });

            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully',
                'status' => 200,

            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Oops! Something went wrong. Try Again!',
            ]);
        }

        // Auth::user()->tokens->each(function ($token, $key) {
        //     $token->delete();
        // });

        // //return $request->Authorization;
        // // $token = $request->user()->token();
        // //return $token;
        // // $token->revoke();
        // // Auth::user()->token()->delete();
        // // $user = $request->user();

        // // foreach ($user->tokens as $token) {
        // //     $token->revoke();
        // // }

        // Auth::logout();

        // return [
        //     'message' => 'User logged out successfully',
        // ];
    }

    public function getAuthUser()
    {
        return Auth::user();
    }
}
