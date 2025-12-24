<?php

namespace App\Models;

use App\Enums\PhotographerAvailability;
use Illuminate\Database\Eloquent\Model;

class Photographer extends Model
{
    //
    protected $fillable = [
        'name',
        'email',
        'phone',
        'is_available',
    ];

    protected $casts = [
        'availability' => PhotographerAvailability::class,
    ];
}