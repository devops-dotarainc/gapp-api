<?php

namespace App\Classes;

use App\Helpers\Cryptor;
use App\Models\ActivityLog;
use App\Helpers\GetIpHelper;
use Illuminate\Support\Facades\Route;

class ActivityLogClass
{
    public static function create($description = null, $model = null, $data = null)
    {
        $data['user_id'] = $data['user_id'] ?? optional(auth()->user())->id;
        $data['role'] = $data['role'] ?? optional(auth()->user())->role;
        $data['status'] = $data['status'] ?? 'success';

        if (!isset($data['controller']) || !isset($data['function']) || !isset($data['module'])) {
            $route = explode('\\', Route::currentRouteAction());
            $data['module'] = $route[1];
            $data['controller'] = explode('@', $route[count($route) - 1])[0];
            $data['function'] = explode('@', $route[count($route) - 1])[1];
        }

        $oldValues = [];
        $newValues = [];

        if ($model) {            
            $model->_id = Cryptor::encrypt($model->id);
            unset($model->id);
            $data['table_data'] = $model;

            foreach ($model->getDirty() as $key => $value) {
                if ($key == 'updated_at') {
                    $model->getDirty($key);
                    continue;
                }
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $model->getDirty()[$key];
            }
            if ($oldValues || $newValues) {
                $oldValues['id'] = Cryptor::encrypt($model->getOriginal('id'));
                $newValues['id'] = Cryptor::encrypt($model->getOriginal('id'));
            }
        }

        $request = request()->all();
        foreach ($request as $key => $value) {
            if (strpos($key, 'password') !== false) {
                $request[$key] = '********';
            }
        }

        if (isset($model->user_id) || (isset($model) && $model->getTable() == 'users')) {
            $request['username'] = isset($model->user_id) ? User::where('id', $model->user_id)->pluck('username')[0] : $model->username ?? null;
        }

        $activityLog = new ActivityLog($data);
        $activityLog->description = $description ?? null;
        $activityLog->table_name = $model ? $model->getTable() : null;
        $activityLog->table_id = $model ? $model->id : null;
        $activityLog->old_value = empty($oldValues) ? null : json_encode($oldValues);
        $activityLog->new_value = empty($newValues) ? null : json_encode($newValues);
        $activityLog->ip = GetIpHelper::getIp();
        $activityLog->request = empty($request) ? null : json_encode($request);

        $activityLog->save();
    }
}