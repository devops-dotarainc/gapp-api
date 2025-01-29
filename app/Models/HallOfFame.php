<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallOfFame extends Model
{
    protected $fillable = [
        'year',
        'image',
        'event_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
