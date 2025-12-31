<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Photographer;
use App\Models\Review;
use App\Models\Studio;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();

        // Pastikan data pendukung ada.
        Customer::factory()->count(25)->create();
        Photographer::factory()->count(8)->create();
        Studio::factory()->count(4)->create();

        $packageIds = Package::query()->pluck('id');
        $photographerIds = Photographer::query()->pluck('id');
        $studioIds = Studio::query()->pluck('id');

        // Buat bookings yang mengacu ke paket yang sudah ada.
        // Pakai status completed agar bisa di-review.
        $bookings = Customer::query()
            ->inRandomOrder()
            ->limit(20)
            ->get()
            ->flatMap(function (Customer $customer) use ($faker, $packageIds, $photographerIds, $studioIds) {
                $count = $faker->numberBetween(1, 3);

                return Booking::factory()
                    ->count($count)
                    ->state(function () use ($faker, $customer, $packageIds, $photographerIds, $studioIds) {
                        return [
                            'customer_id' => $customer->id,
                            'package_id' => $packageIds->random(),
                            'photographer_id' => $faker->boolean(80) ? $photographerIds->random() : null,
                            'studio_id' => $faker->boolean(80) ? $studioIds->random() : null,
                            'status' => BookingStatus::Completed,
                            'scheduled_at' => $faker->dateTimeBetween('-2 months', '-1 day'),
                        ];
                    })
                    ->create();
            });

        // Buat review untuk sebagian booking completed (unik per booking).
        $bookings
            ->shuffle()
            ->take((int) round($bookings->count() * 0.8))
            ->each(function (Booking $booking) use ($faker) {
                Review::query()->create([
                    'booking_id' => $booking->id,
                    'customer_id' => $booking->customer_id,
                    'package_id' => $booking->package_id,
                    'rating' => $faker->numberBetween(1, 5),
                    'comment' => $faker->boolean(70) ? $faker->sentence() : null,
                ]);
            });

        // Update packages.rating berdasarkan rata-rata review
        $packageRatings = Review::query()
            ->selectRaw('package_id, AVG(rating) as avg_rating')
            ->groupBy('package_id')
            ->get();

        foreach ($packageRatings as $row) {
            Package::query()
                ->whereKey($row->package_id)
                ->update(['rating' => round((float) $row->avg_rating, 2)]);
        }
    }
}
