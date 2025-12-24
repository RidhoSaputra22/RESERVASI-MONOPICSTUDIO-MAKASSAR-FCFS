<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasCodeGenerated
{
    /**
     * Generate unique code for model.
     *
     * @param string $prefix
     * @param int $length
     * @return string
     */
    public static function generateCode(string $prefix, int $length = 3): string
    {
        $latest = static::orderBy('id', 'desc')->first();
        $nextNumber = $latest ? $latest->id + 1 : 1;
        $formatted = str_pad($nextNumber, $length, '0', STR_PAD_LEFT);

        return strtoupper("{$prefix}-{$formatted}");
    }
}