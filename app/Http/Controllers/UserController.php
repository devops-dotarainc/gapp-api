<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\ActivityLogClass;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\ShowRequest;
use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Requests\User\DeleteRequest;
use App\Http\Requests\User\UpdateRequest;
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

            if (isset($request['search'])) {
                $search = $request['search'];
    
                $users = $users->where('username', 'LIKE', "%$search%")
                    ->orWhere('contact_number', 'LIKE', "%$search%");
            }

            ActivityLogClass::create('Get User Data', $users);

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
            \Log::error($exception);

            ActivityLogClass::create('Get User Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

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

            ActivityLogClass::create('Create User', $user);

            return new ApiSuccessResponse(
                $user,
                [
                    'message' => 'User created succesfully!',
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Create User Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

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

            ActivityLogClass::create('Show User Data', $user);

            return new ApiSuccessResponse(
                $user,
                [
                    'message' => 'User retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Show User Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

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
                ActivityLogClass::create('Update User Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'User not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            Gate::authorize('update', $user);

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

            if (isset($request['contact_number'])) {
                $user->contact_number = $request['contact_number'];
            }

            if (isset($request['role'])) {
                $user->role = $request['role'];
            }

            if (isset($request['password'])) {
                //password confirmation

                if ($user->password === $request['password']) {
                    ActivityLogClass::create('Update User Failed', null, [
                        'user_id' => auth()->user()->id ?? null,
                        'role' => auth()->user()->role->value ?? null,
                        'status' => 'error',
                    ]);

                    return new ApiErrorResponse(
                        'Cannot use current password as new password!',
                        Response::HTTP_BAD_REQUEST,
                    );
                }

                $user->password = Hash::make($request['password']);

                $user->tokens()->delete();
            }

            if($user->isClean()) {
                ActivityLogClass::create('Update User Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'No changes made.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            ActivityLogClass::create('Update User', $user);

            $user->save();
            
            return new ApiSuccessResponse(
                $user,
                [
                    'message' => 'User updated succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Update User Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

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

            if(!$user){
                return new ApiErrorResponse(
                    'User does not exist!',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            Gate::authorize('delete', $user);

            if(auth()->user()->id === $user->id) {
                ActivityLogClass::create('Delete User Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'Cannot delete your own account!',
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $user->tokens()->delete();

            ActivityLogClass::create('Delete User', $user);

            $user->delete();

            return new ApiSuccessResponse(
                null,
                [
                    'message' => 'User deleted successfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Delete User Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to delete a user!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }
}
