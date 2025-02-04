<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'title',
        'description',
        'image',
        'background_color',
        'event_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
