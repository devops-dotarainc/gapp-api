<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stag extends Model
{
    protected $table = 'stags';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'stag_registry',
        'farm_name',
        'farm_address',
        'breeder_name',
        'chapter',
        'banded_cockerels',
    ];
}
