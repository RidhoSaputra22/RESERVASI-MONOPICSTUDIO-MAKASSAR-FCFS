<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiFoto extends Model
{
    //

    protected $fillable = [
        'name',
        'session_time',
    ];

    protected $casts = [
        'session_time' => 'string',
    ];


}
