<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'user_id',
        'role',
        'status',
        'module',
        'controller',
        'function',
        'description',
        'table_name',
        'table_id',
        'old_value',
        'new_value',
        'ip',
        'request',
    ];
}
