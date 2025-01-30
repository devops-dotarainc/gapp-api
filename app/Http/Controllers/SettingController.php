<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\ActivityLogClass;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Requests\Setting\ShowRequest;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Setting\IndexRequest;
use App\Http\Requests\Setting\StoreRequest;
use App\Http\Requests\Setting\DeleteRequest;
use App\Http\Requests\Setting\UpdateRequest;

class SettingController extends Controller
{
    public function index(IndexRequest $request)
    {
        try {
            $settings = new Setting();

            $sort = $request['sort'] ?? 'id';

            $order = $request['order'] ?? 'desc';

            $limit = $request['limit'] ?? 50;

            if (isset($request['key'])) {
                $settings = $settings->where('key', $request['key']);
            }

            if (isset($request['name'])) {
                $settings = $settings->where('name', $request['name']);
            }

            if (isset($request['value'])) {
                $settings = $settings->where('value', $request['value']);
            }

            if (isset($request['search'])) {
                $search = $request['search'];

                $settings = $settings->where('key', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->orWhere('value', 'LIKE', "%$search%");
            }

            ActivityLogClass::create('Get Setting Data');

            $settings = $settings->orderBy($sort, $order)
                ->paginate($limit);

            $settings->getCollection()->transform(function ($setting) {
                return $setting;
            });

            return new ApiSuccessResponse(
                $settings,
                [
                    'message' => 'Settings retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Get Setting Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to list all Settings!',
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

            $setting = Setting::create($data);

            ActivityLogClass::create('Create Setting', $setting);

            return new ApiSuccessResponse(
                $setting,
                [
                    'message' => 'Setting created succesfully!',
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Create Setting Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to create an Setting!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }

    }

    public function show(ShowRequest $request, $id)
    {
        try {
            $setting = Setting::find($id);

            if (!$setting) {
                return new ApiErrorResponse(
                    'Setting not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            ActivityLogClass::create('Show Setting Data', $setting);

            return new ApiSuccessResponse(
                $setting,
                [
                    'message' => 'Setting retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Show Setting Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to show Setting!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $setting = Setting::find($id);

            if (!$setting) {
                ActivityLogClass::create('Update Setting Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'Setting not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            Gate::authorize('update', $setting);

            if (isset($request['key'])) {
                $setting->key = $request['key'];
            }

            if (isset($request['name'])) {
                $setting->name = $request['name'];
            }

            if (isset($request['value'])) {
                $setting->value = $request['value'];
            }

            if ($setting->isClean()) {
                ActivityLogClass::create('Update Setting Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'No changes made.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            $setting->updated_by = auth()->user()->id;
            $setting->updated_at = Carbon::now()->format('Y-m-d H:i:s.u');

            ActivityLogClass::create('Update Setting', $setting);

            $setting->save();

            return new ApiSuccessResponse(
                $setting,
                [
                    'message' => 'Setting updated succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Update Setting Failed', null, [
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
            $setting = Setting::find($id);

            if (!$setting) {
                return new ApiErrorResponse(
                    'Setting does not exist!',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            Gate::authorize('delete', $setting);

            ActivityLogClass::create('Delete Setting', $setting);

            $setting->delete();

            return new ApiSuccessResponse(
                null,
                [
                    'message' => 'Setting deleted successfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Delete Setting Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to delete a Setting!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }
}
