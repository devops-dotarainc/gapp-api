<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $table = 'chapters';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'chapter',
        'banded_cockerels',
    ];
}
