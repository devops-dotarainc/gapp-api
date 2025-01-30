<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Binding extends Model
{
    protected $table = 'bindings';

    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = [
        'year',
        'image',
        'event_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
