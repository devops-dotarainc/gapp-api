<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Wingband;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
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

        $totalWingband = Wingband::where('created_by', $user->id)->count();

        return new ApiSuccessResponse(
            [
                'username' => $user->username,
                'role' => $user->role->label(),
                'contact_number' => $user->contact_number,
                'created_at' => $user->created_at,
                'created_by' => $user->created_by,
                'total_wingband' => $totalWingband,
            ],
            [
                'message' => 'Profile retrieved successfully!',
            ],
            Response::HTTP_OK,
        );
    }

    public function changePassword(ChangePasswordRequest $request){
        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return new ApiErrorResponse(
                'Current password incorrect',
                Response::HTTP_BAD_REQUEST,
            );
        }

        if ($request->password === $request->new_password) {
            return new ApiErrorResponse(
                'You cannot use your current password as your new password!',
                Response::HTTP_BAD_REQUEST,
            );
        }

        $user->password = Hash::make($request->new_password);

        $user->save();

        return new ApiSuccessResponse(
            [
                'user' => $user->username
            ],
            [
                'message' => 'Password changed successfully!',
            ],
            Response::HTTP_OK,
        );
    }

    public function delete($id) {
        $user = User::find($id);

        if(!$user){
            return new ApiErrorResponse(
                'User does not exist!',
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        if(auth()->user()->id === $user->id) {
            return new ApiErrorResponse(
                'Cannot delete your own account!',
                Response::HTTP_BAD_REQUEST,
            );
        }

        $user->tokens()->delete();

        $user->delete();

        return new ApiSuccessResponse(
            null,
            [
                'message' => 'User deleted successfully!',
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
