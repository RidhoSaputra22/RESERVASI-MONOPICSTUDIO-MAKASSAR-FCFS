<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Package extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'fasilitas',
        'photo',
        'price',
        'rating',
        'duration_minutes',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'duration_minutes' => 'integer',
        'fasilitas' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $package) {
            if (!$package->slug || $package->isDirty('name')) {
                $base = Str::slug((string) $package->name);
                if ($base === '') {
                    $base = 'package';
                }

                $slug = $base;
                $counter = 2;
                while (
                    static::query()
                        ->where('slug', $slug)
                        ->when($package->exists, fn ($q) => $q->where('id', '!=', $package->id))
                        ->exists()
                ) {
                    $slug = $base . '-' . $counter;
                    $counter++;
                }

                $package->slug = $slug;
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
