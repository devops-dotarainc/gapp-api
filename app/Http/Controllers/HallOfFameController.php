<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\HallOfFame;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\ActivityLogClass;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\HallOfFame\ShowRequest;
use App\Http\Requests\HallOfFame\IndexRequest;
use App\Http\Requests\HallOfFame\StoreRequest;
use App\Http\Requests\HallOfFame\DeleteRequest;
use App\Http\Requests\HallOfFame\UpdateRequest;

class HallOfFameController extends Controller
{
    public function index(IndexRequest $request)
    {
        try {
            $fames = new HallOfFame();

            $sort = $request['sort'] ?? 'id';

            $order = $request['order'] ?? 'desc';

            $limit = $request['limit'] ?? 50;

            if (isset($request['year'])) {
                $fames = $fames->where('year', $request['year']);
            }

            if (isset($request['search'])) {
                $search = $request['search'];

                $fames = $fames->where('year', 'LIKE', "%$search%");
            }

            ActivityLogClass::create('Get HallOfFame Data');

            $fames = $fames->orderBy($sort, $order)
                ->paginate($limit);

            $fames->getCollection()->transform(function ($fame) {
                return $fame;
            });

            return new ApiSuccessResponse(
                $fames,
                [
                    'message' => 'HallOfFame retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Get HallOfFame Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to list all HallOfFame!',
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

            $fame = HallOfFame::create($data);

            ActivityLogClass::create('Create HallOfFame', $fame);

            return new ApiSuccessResponse(
                $fame,
                [
                    'message' => 'Fame created succesfully!',
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Create HallOfFame Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to create an HallOfFame!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }

    }

    public function show(ShowRequest $request, $id)
    {
        try {
            $fame = HallOfFame::find($id);

            if (!$fame) {
                return new ApiErrorResponse(
                    'HallOfFame not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            ActivityLogClass::create('Show HallOfFame Data', $fame);

            return new ApiSuccessResponse(
                $fame,
                [
                    'message' => 'HallOfFame retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Show HallOfFame Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to show HallOfFame!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $fame = HallOfFame::find($id);

            if (!$fame) {
                ActivityLogClass::create('Update HallOfFame Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'HallOfFame not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            Gate::authorize('update', $fame);

            if (isset($request['year'])) {
                $fame->year = $request['year'];
            }

            if ($fame->isClean()) {
                ActivityLogClass::create('Update HallOfFame Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'No changes made.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            ActivityLogClass::create('Update HallOfFame', $fame);

            $fame->save();

            return new ApiSuccessResponse(
                $fame,
                [
                    'message' => 'HallOfFame updated succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Update HallOfFame Failed', null, [
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
            $fame = HallOfFame::find($id);

            if (!$fame) {
                return new ApiErrorResponse(
                    'HallOfFame does not exist!',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            Gate::authorize('delete', $fame);

            ActivityLogClass::create('Delete HallOfFame', $fame);

            $fame->delete();

            return new ApiSuccessResponse(
                null,
                [
                    'message' => 'HallOfFame deleted successfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Delete HallOfFame Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to delete a HallOfFame!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }
}
