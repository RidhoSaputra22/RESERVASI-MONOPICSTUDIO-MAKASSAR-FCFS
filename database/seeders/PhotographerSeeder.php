<?php

namespace Database\Seeders;

use App\Models\Photographer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PhotographerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test photographer with login credentials
        Photographer::updateOrCreate(
            ['email' => 'photographer@monopic.com'],
            [
                'name' => 'Test Photographer',
                'email' => 'photographer@monopic.com',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'is_available' => true,
            ]
        );

        // Create additional photographers with passwords
        Photographer::updateOrCreate(
            ['email' => 'andi@monopic.com'],
            [
                'name' => 'Andi Photographer',
                'email' => 'andi@monopic.com',
                'phone' => '081234567891',
                'password' => Hash::make('password'),
                'is_available' => true,
            ]
        );

        Photographer::updateOrCreate(
            ['email' => 'budi@monopic.com'],
            [
                'name' => 'Budi Photographer',
                'email' => 'budi@monopic.com',
                'phone' => '081234567892',
                'password' => Hash::make('password'),
                'is_available' => true,
            ]
        );
    }
}
