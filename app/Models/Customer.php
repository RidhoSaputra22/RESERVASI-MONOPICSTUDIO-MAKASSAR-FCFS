<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    //

    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
