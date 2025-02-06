<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Helpers\Cryptor;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use App\Classes\ActivityLogClass;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\ActivityLog\IndexRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ActivityLogController extends Controller
{
    public function index(IndexRequest $request)
    {
        try {
            $sort = $validated['sort'] ?? 'id';

            $order = $validated['order'] ?? 'desc';

            $limit = $validated['limit'] ?? 50;

            $activityLogs = ActivityLog::with('user:id,username');

            if(isset($request->username)) {
                $activityLogs->whereRelation('user', 'username', $request->username);
            }

            if(isset($request->status)) {
                $activityLogs->where('status', $request->status);
            }

            if(isset($request->role)) {
                $activityLogs->where('role', $request->role);
            }

            if(isset($request->description)) {
                $activityLogs->where('description', $request->description);
            }

            if(isset($request->date)) {
                $activityLogs->whereDate('created_at', $request->date);
            }

            if (isset($from) && isset($to)) {
                $from = date($from);

                $to = date($to);

                $activityLogs->whereBetween('created_at', [Carbon::parse($from.'00:00:00'), Carbon::parse($to.'23:59:59')]);
            }

            if(isset($request->search)) {
                $search = $request->search;

                $activityLogs->where(function (Builder $query) use ($search) {
                    $query->where('controller', 'LIKE', "%$search%")
                    ->orWhere('function', 'LIKE', "%$search%")
                    ->orWhere('table_name', 'LIKE', "%$search%")
                    ->orWhere('table_id', 'LIKE', "%$search%");
                });
            }

            if($activityLogs->doesntExist()) {
                ActivityLogClass::create('Get Activity Logs Failed', null, [
                    'user_id' => auth()->user()->id ?? null,
                    'role' => auth()->user()->role->value ?? null,
                    'status' => 'error',
                ]);

                return new ApiErrorResponse(
                    'No activity logs found.',
                    Response::HTTP_NOT_FOUND
                );
            }

            $data = $activityLogs->orderBy($sort, $order)->paginate($limit);

            $data->getCollection()->transform(function ($activity) {
                $activity->table_id = Cryptor::encrypt($activity->id);
                $activity->role ? $activity->role = Role::fromValue($activity->role) : null;
                $activity->user ? $activity->username = $activity->user->username : null;
                $activity->_id = Cryptor::encrypt($activity->id);

                unset($activity->id, $activity->user_id, $activity->user);

                return $activity;
            });

            ActivityLogClass::create('Get Activity Logs Data');

            return new ApiSuccessResponse(
                $data,
                ['message' => 'Activity logs retrieved successfully!'],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            \Log::error($e);

            ActivityLogClass::create('Get Activity Logs Data Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured when trying to display all activity logs!',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e
            );
        }
    }
}
