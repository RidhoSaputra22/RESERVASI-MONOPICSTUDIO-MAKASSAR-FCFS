<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
          Category::factory()->count(5)
            ->has(Package::factory()->count(10))
            ->create();
    }
}
