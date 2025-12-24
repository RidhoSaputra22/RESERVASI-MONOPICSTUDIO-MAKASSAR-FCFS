<?php

namespace App\Enums;

enum PhotographerAvailability: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Off = 'off';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Busy => 'Sedang Bekerja',
            self::Off => 'Libur',
        };
    }
}