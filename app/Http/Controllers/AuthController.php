<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $roles = [
            1 => 'admin',
            2 => 'viewer',
            3 => 'encoder',
        ];

        $user = new User;

        $user = $user->where('username', $request['username'])
            ->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            return new ApiErrorResponse(
                'Username/Password is incorrect',
                Response::HTTP_UNAUTHORIZED,
            );
        }

        $user->tokens()->delete();

        $user->last_login_at = now()->format('Y-m-d H:i:s.u');
        $user->last_login_ip = $request->getClientIp();

        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return new ApiSuccessResponse(
            [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'role' => $user->role->label(),
            ],
            [
                'message' => 'You are now logged in!',
            ],
            Response::HTTP_OK,
        );
    }

    public function profile()
    {
        $user = auth()->user();

        // $user['role'] = $user->role->label();

        return new ApiSuccessResponse(
            [
                'username' => $user->username,
                'role' => $user->role->label(),
                'created_at' => $user->created_at,
                'created_by' => $user->created_by,
            ],
            [
                'message' => 'Profile retrieved successfully!',
            ],
            Response::HTTP_OK,
        );

    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        auth()->user()->save();

        return new ApiSuccessResponse(
            null,
            [
                'message' => 'Logged out successfully!',
            ],
            Response::HTTP_OK,
        );

    }


}
