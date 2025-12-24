<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'type',
        'message',
        'is_sent',
    ];

    protected $casts = [
        'type' => NotificationType::class,
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
