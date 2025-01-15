<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wingband extends Model
{
    protected $table = 'wingbands';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'stag_registry',
        'breeder_name',
        'farm_name',
        'farm_address',
        'province',
        'wingband_number',
        'feather_color',
        'leg_color',
        'comb_shape',
        'nose_markings',
        'feet_markings',
        'season',
        'status',
        'wingband_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
