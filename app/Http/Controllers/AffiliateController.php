<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Affiliate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\ActivityLogClass;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Affiliate\ShowRequest;
use App\Http\Requests\Affiliate\IndexRequest;
use App\Http\Requests\Affiliate\StoreRequest;
use App\Http\Requests\Affiliate\DeleteRequest;
use App\Http\Requests\Affiliate\UpdateRequest;

class AffiliateController extends Controller
{
    public function index(IndexRequest $request)
    {
        try {
            $affiiliates = new Affiliate;

            $sort = $request['sort'] ?? 'id';

            $order = $request['order'] ?? 'desc';

            $limit = $request['limit'] ?? 50;

            if (isset($request['name'])) {
                $affiiliates = $affiiliates->where('name', $request['name']);
            }

            if (isset($request['location'])) {
                $affiiliates = $affiiliates->where('location', $request['location']);
            }

            if (isset($request['contact_number'])) {
                $affiiliates = $affiiliates->where('contact_number', $request['contact_number']);
            }

            if (isset($request['search'])) {
                $search = $request['search'];

                $affiiliates = $affiiliates->where('name', 'LIKE', "%$search%")
                    ->orWhere('location', 'LIKE', "%$search%")
                    ->orWhere('contact_number', 'LIKE', "%$search%");
            }

            ActivityLogClass::create('Get Affiliate Data');

            $affiiliates = $affiiliates->orderBy($sort, $order)
                ->paginate($limit);

            $affiiliates->getCollection()->transform(function ($affiiliate) {
                return $affiiliate;
            });

            return new ApiSuccessResponse(
                $affiiliates,
                [
                    'message' => 'Affiliates retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Get Affiliate Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to list all affiliates!',
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

            if (isset($request['image'])) {
                $image = $request['image'];

                $imageName = "gapp-image" . "-" . Carbon::now()->format("YmdHis") . '.' . $image->getClientOriginalExtension();

                Storage::disk('local')->put("gapp/{$imageName}", file_get_contents($image));

                $request['image'] = $imageName;
            }

            $data = $request->all();

            $data['image'] = $imageName;

            $affiliate = Affiliate::create($data);

            ActivityLogClass::create('Create Affiliate', $affiliate);

            return new ApiSuccessResponse(
                $affiliate,
                [
                    'message' => 'Affiliate created succesfully!',
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Create Affiliate Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to create an affiliate!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }

    }

    public function show(ShowRequest $request, $id)
    {
        try {
            $affiliate = Affiliate::find($id);

            if (!$affiliate) {
                return new ApiErrorResponse(
                    'Affiliate not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            ActivityLogClass::create('Show Affiliate Data', $affiliate);

            return new ApiSuccessResponse(
                $affiliate,
                [
                    'message' => 'Affiliate retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Show Affiliate Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to show an affiliate!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $affiliate = Affiliate::find($id);

            if (!$affiliate) {
                ActivityLogClass::create('Update Affiliate Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'Affiliate not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            Gate::authorize('update', $affiliate);

            if (isset($request['name'])) {
                $affiliate->name = $request['name'];
            }

            if (isset($request['location'])) {
                $affiliate->location = $request['location'];
            }

            if (isset($request['contact_number'])) {
                $affiliate->contact_number = $request['contact_number'];
            }

            if ($affiliate->isClean()) {
                ActivityLogClass::create('Update Affiliate Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'No changes made.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            ActivityLogClass::create('Update Affiliate', $affiliate);

            $affiliate->save();

            return new ApiSuccessResponse(
                $affiliate,
                [
                    'message' => 'Affiliate updated succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Update Affiliate Failed', null, [
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
            $affiiliate = Affiliate::find($id);

            if (!$affiiliate) {
                return new ApiErrorResponse(
                    'Affiliate does not exist!',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            Gate::authorize('delete', $affiiliate);

            ActivityLogClass::create('Delete Affiliate', $affiiliate);

            $affiiliate->delete();

            return new ApiSuccessResponse(
                null,
                [
                    'message' => 'Affiliate deleted successfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Delete Affiliate Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to delete an affiliate!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }
}
