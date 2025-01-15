<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Breeder extends Model
{
    protected $table = 'breeders';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'name',
        'farm_name',
        'farm_address',
        'chapter',
        'banded_cockerels',
    ];
}
