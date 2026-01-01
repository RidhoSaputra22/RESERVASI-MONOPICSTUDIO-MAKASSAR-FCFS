<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    Booking::factory()
        ->count(20)

        ->create();
    }
}
