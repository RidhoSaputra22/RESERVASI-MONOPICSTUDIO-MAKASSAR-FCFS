<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Photographer = 'photographer';
    case Customer = 'customer';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
