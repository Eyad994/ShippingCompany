<?php

namespace App\Http\Controllers;

use App\Login;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $rules = array(
            'first_name' => 'required|min:3|max:20',
            'last_name' => 'required|min:3|max:20',
            'cellular_number' => 'required|min:3|max:20||regex:/(07)[7-9]{1}[0-9]{7}/',
            'email' => 'required|string|email|unique:users',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'max:16',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'
            ]
            //'password' => 'required|confirmed|min:8|max:16'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return ['status' => 422, 'errors' => $validator->errors()];
        }

        $user = new User([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'cellular_number' => $request->cellular_number,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $user->save();
        return response()->json([
            'user' => $user
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] access_token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'The login process was unsuccessful'
            ], 401);

        $user = $request->user();

        $agent = new Agent();

        Login::create([
           'user_id' => $user['id'],
           'ip_number' => \Request::ip(),
            'country' => Location::get(\Request::ip()),
            'system' => $agent->platform(),
            'device' => $agent->device()
        ]);

        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if ($request->remember_me)

            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
