<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'address',
        'telephone_number',
        'email',
        'twitter_url',
        'facebook_url',
        'youtube_url',
        'linkedin_url',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

}
