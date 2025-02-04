<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\ActivityLogClass;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Schedule\ShowRequest;
use App\Http\Requests\Schedule\IndexRequest;
use App\Http\Requests\Schedule\StoreRequest;
use App\Http\Requests\Schedule\DeleteRequest;
use App\Http\Requests\Schedule\UpdateRequest;

class ScheduleController extends Controller
{
    public function index(IndexRequest $request)
    {
        try {
            $schedules = new Schedule();

            $sort = $request['sort'] ?? 'id';

            $order = $request['order'] ?? 'desc';

            $limit = $request['limit'] ?? 50;

            if (isset($request['title'])) {
                $schedules = $schedules->where('title', $request['title']);
            }

            if (isset($request['description'])) {
                $schedules = $schedules->where('description', $request['description']);
            }

            if (isset($request['background_color'])) {
                $schedules = $schedules->where('background_color', $request['background_color']);
            }

            if (isset($request['search'])) {
                $search = $request['search'];

                $schedules = $schedules->where('title', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%")
                    ->orWhere('background_color', 'LIKE', "%$search%");
            }

            ActivityLogClass::create('Get Schedule Data');

            $schedules = $schedules->orderBy($sort, $order)
                ->paginate($limit);

            $schedules->getCollection()->transform(function ($schedule) {
                return $schedule;
            });

            return new ApiSuccessResponse(
                $schedules,
                [
                    'message' => 'Schedules retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Get Schedule Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to list all schedules!',
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

            $schedule = Schedule::create($data);

            ActivityLogClass::create('Create Schedule', $schedule);

            return new ApiSuccessResponse(
                $schedule,
                [
                    'message' => 'Schedule created succesfully!',
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Create Schedule Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to create an schedule!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }

    }

    public function show(ShowRequest $request, $id)
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return new ApiErrorResponse(
                    'Schedule not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            ActivityLogClass::create('Show Schedule Data', $schedule);

            return new ApiSuccessResponse(
                $schedule,
                [
                    'message' => 'Schedule retrieved succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Show Schedule Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to show an schedule!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                ActivityLogClass::create('Update Schedule Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'Schedule not found!',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            Gate::authorize('update', $schedule);

            if (isset($request['title'])) {
                $schedule->title = $request['title'];
            }

            if (isset($request['description'])) {
                $schedule->description = $request['description'];
            }

            if (isset($request['background_color'])) {
                $schedule->background_color = $request['background_color'];
            }

            if (isset($request['image'])) {
                $image = $request['image'];

                $imageName = "gapp-image" . "-" . Carbon::now()->format("YmdHis") . '.' . $image->getClientOriginalExtension();

                Storage::put("gapp/{$imageName}", file_get_contents($image));

                $schedule->image = $imageName;
            }

            if ($schedule->isClean()) {
                ActivityLogClass::create('Update Schedule Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'No changes made.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            $schedule->updated_by = auth()->user()->id;
            $schedule->updated_at = Carbon::now()->format('Y-m-d H:i:s.u');

            ActivityLogClass::create('Update Schedule', $schedule);

            $schedule->save();

            return new ApiSuccessResponse(
                $schedule,
                [
                    'message' => 'Schedule updated succesfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Update Schedule Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to update a schedule!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    public function delete(DeleteRequest $request, $id)
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return new ApiErrorResponse(
                    'Schedule does not exist!',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }

            Gate::authorize('delete', $schedule);

            ActivityLogClass::create('Delete Schedule', $schedule);

            $schedule->delete();

            return new ApiSuccessResponse(
                null,
                [
                    'message' => 'Schedule deleted successfully!',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $exception) {
            \Log::error($exception);

            ActivityLogClass::create('Delete Schedule Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to delete an schedule!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }
}
