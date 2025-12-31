<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];


    protected static function booted(): void
    {
        static::saving(function (self $category) {
            if (!$category->slug || $category->isDirty('name')) {
                $base = \Illuminate\Support\Str::slug((string) $category->name);
                if ($base === '') {
                    $base = 'category';
                }

                $slug = $base;
                $counter = 2;
                while (
                    static::query()
                        ->where('slug', $slug)
                        ->when($category->exists, fn ($q) => $q->where('id', '!=', $category->id))
                        ->exists()
                ) {
                    $slug = $base . '-' . $counter;
                    $counter++;
                }

                $category->slug = $slug;
            }
        });
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

}
