<?php

namespace Database\Seeders;

use App\Models\Photographer;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'user',
            'email' => 'user@gmail.com',
            'hp' => '081234567890',
            'password' => Hash::make('user'),
            'photo' => null,
        ]);

        User::factory()->create([
            'name' => 'Ridho Saputra',
            'email' => 'saputra22022@gmail.com',
            'hp' => '081344968521',
            'password' => Hash::make('ridho123123'),
            'photo' => null,
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'hp' => '081234567891',
            'password' => Hash::make('admin'),
            'photo' => null,
        ]);

        // Photographer::factory(1)->create(
        //     ['is_available' => true]
        // );
        // Studio::factory(1)->create();

        $this->call([
            // CategorySeeder::class,
            // ReviewSeeder::class,
            // BookingSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
