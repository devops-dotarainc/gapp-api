<?php

namespace App\Models;

use App\Enums\Season;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wingband extends Model
{
    use SoftDeletes;
    
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
        'chapter',
        'contact_number',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'season' => Season::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
