<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Binding;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\ActivityLogClass;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Requests\Binding\ShowRequest;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Binding\IndexRequest;
use App\Http\Requests\Binding\StoreRequest;
use App\Http\Requests\Binding\DeleteRequest;
use App\Http\Requests\Binding\UpdateRequest;

class BindingController extends Controller
{
    public function index(IndexRequest $request)
    {
        try {
            $bindings = new Binding();

            $sort = $request['sort'] ?? 'id';

            $order = $request['order'] ?? 'desc';

            $limit = $request['limit'] ?? 50;

            if (isset($request['year'])) {
                $bindings = $bindings->where('year', $request['year']);
            }

            if (isset($request['search'])) {
                $search = $request['search'];

                $bindings = $bindings->where('year', 'LIKE', "%$search%");
            }

            ActivityLogClass::create('Get Binding Data');

            $bindings = $bindings->orderBy($sort, $order)
                ->paginate($limit);

            $bindings->getCollection()->transform(function ($binding) {
                return $binding;
            });

            return new ApiSuccessResponse(
                $bindings,
                [
                    'message' => 'Bindings retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Get Binding Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to list all Bindings!',
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

            $data = $request->all();

            if (isset($request['image'])) {
                $image = $request['image'];

                $imageName = "gapp-image" . "-" . Carbon::now()->format("YmdHis") . '.' . $image->getClientOriginalExtension();

                Storage::put("gapp/{$imageName}", file_get_contents($image));

                $data['image'] = $imageName;
            }

            $binding = Binding::create($data);

            ActivityLogClass::create('Create Binding', $binding);

            return new ApiSuccessResponse(
                $binding,
                [
                    'message' => 'Binding created succesfully!',
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Create Binding Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to create an Binding!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }

    }

    public function show(ShowRequest $request, $id)
    {
        try {
            $binding = Binding::find($id);

            if (!$binding) {
                return new ApiErrorResponse(
                    'Binding not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            ActivityLogClass::create('Show Binding Data', $binding);

            return new ApiSuccessResponse(
                $binding,
                [
                    'message' => 'Binding retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Show Binding Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to show Binding!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $binding = Binding::find($id);

            if (!$binding) {
                ActivityLogClass::create('Update Binding Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'Binding not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            Gate::authorize('update', $binding);

            if (isset($request['year'])) {
                $binding->year = $request['year'];
            }

            if (isset($request['event_date'])) {
                $binding->event_date = $request['event_date'];
            }

            if ($binding->isClean()) {
                ActivityLogClass::create('Update Binding Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'No changes made.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            $binding->updated_by = auth()->user()->id;
            $binding->updated_at = Carbon::now()->format('Y-m-d H:i:s.u');

            ActivityLogClass::create('Update Binding', $binding);

            $binding->save();

            return new ApiSuccessResponse(
                $binding,
                [
                    'message' => 'Binding updated succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Update Binding Failed', null, [
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
            $binding = Binding::find($id);

            if (!$binding) {
                return new ApiErrorResponse(
                    'Binding does not exist!',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            Gate::authorize('delete', $binding);

            ActivityLogClass::create('Delete Binding', $binding);

            $binding->delete();

            return new ApiSuccessResponse(
                null,
                [
                    'message' => 'Binding deleted successfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Delete Binding Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to delete a Binding!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }
}
