<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\DeleteRequest;
use App\Http\Requests\User\UpdateRequest;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\ShowRequest;
use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;

class UserController extends Controller
{
    public function index(IndexRequest $request)
    {
        try {
            $users = new User;

            $sort = $request['sort'] ?? 'id';

            $order = $request['order'] ?? 'desc';

            $limit = $request['limit'] ?? 50;

            if (isset($request['username'])) {
                $users = $users->where('username', $request['username']);
            }

            $users = $users->orderBy($sort, $order)
                ->paginate($limit);

            $users->getCollection()->transform(function ($user) {
                return $user;
            });

            return new ApiSuccessResponse(
                $users,
                [
                    'message' => 'Users retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            return new ApiErrorResponse(
                'An error occured when trying to list all users!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function store(StoreRequest $request)
    {
        try {
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

    public function show(ShowRequest $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return new ApiErrorResponse(
                    'User not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            return new ApiSuccessResponse(
                $user,
                [
                    'message' => 'User retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            return new ApiErrorResponse(
                'An error occured when trying to list all users!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return new ApiErrorResponse(
                    'User not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            if (isset($request['username'])) {
                $user->username = $request['username'];
            }

            if (isset($request['first_name'])) {
                $user->first_name = $request['first_name'];
            }

            if (isset($request['last_name'])) {
                $user->last_name = $request['last_name'];
            }

            if (isset($request['email'])) {
                $user->email = $request['email'];
            }

            if (isset($request['role'])) {
                $user->role = $request['role'];
            }

            if (isset($request['password'])) {
                //password confirmation
                $user->password = $request['password'];
            }

            $user->save();

            return new ApiSuccessResponse(
                $user,
                [
                    'message' => 'User updated succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            return new ApiErrorResponse(
                'An error occured when trying to update a user!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function delete(DeleteRequest $request, $id)
    {
        try {
            $user = User::find($id);

            $user->delete();

            return new ApiSuccessResponse(
                $user,
                [
                    'message' => 'User deleted succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            return new ApiErrorResponse(
                'An error occured when trying to delete a user!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }
}
