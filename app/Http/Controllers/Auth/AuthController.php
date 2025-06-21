<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\LoginToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'USER',
            ]);

            $token = Str::random(60);
            LoginToken::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => Carbon::now()->addDays(7),
            ]);

            return $this->sendJsonResponse(true, 'User registered successfully', [
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->sendJsonResponse(false, 'Invalid credentials', [], 401);
            }

            $token = Str::random(60);
            LoginToken::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => Carbon::now()->addDays(7),
            ]);

             $user->access_token = $token;

              return $this->sendJsonResponse(true, 'Login Successfully', $user);
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            LoginToken::where('token', $token)->delete();
            return $this->sendJsonResponse(true, 'Logout successful');
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }
}