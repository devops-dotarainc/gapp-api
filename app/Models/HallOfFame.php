<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallOfFame extends Model
{
    protected $fillable = [
        'year',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
