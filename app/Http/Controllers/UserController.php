<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\StoreRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;

class UserController extends Controller
{
    public function list()
    {
        //
    }

    public function store(StoreRequest $request)
    {
        try {
            $request['password'] = Hash::make($request['pasword']);
            $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s.u');
            $request['created_by'] = auth()->user()->id;

            $user = User::create($request->all());

            return new ApiSuccessResponse(
                $user,
                [
                    'message' => 'User created succesfully!',
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $exception) {
            return new ApiErrorResponse(
                'An error occured when trying to create a user!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }

    }
}
