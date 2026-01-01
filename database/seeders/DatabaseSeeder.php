<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Package;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);



        $this->call([
            CategorySeeder::class,
            ReviewSeeder::class,
            // BookingSeeder::class,
        ]);
    }
}
