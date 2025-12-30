<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Photographer extends Model
{
    //

    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];
}
