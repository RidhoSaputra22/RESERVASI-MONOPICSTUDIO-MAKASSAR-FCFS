<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Photographer;
use App\Models\Studio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $slotTimes = \App\Models\SesiFoto::orderBy('session_time')
            ->pluck('session_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();
        $package = Package::factory()->create();
        $scheduledTime = fake()->randomElement($slotTimes);
        $scheduledAt = Carbon::createFromTime(
            (int) explode(':', $scheduledTime)[0],
            (int) explode(':', $scheduledTime)[1],
            0,
        )->addMinutes($package->duration_minutes);


        return [
            'customer_id' => Customer::factory(),
            'package_id' => $package->id,
            'photographer_id' => fake()->boolean(80) ? Photographer::factory() : null,
            'studio_id' => fake()->boolean(80) ? Studio::factory() : null,
            'scheduled_at' => $scheduledAt,
            'snap_token' => fake()->optional()->uuid(),
            'status' => fake()->randomElement(BookingStatus::cases()),
            'code' => null,
        ];
    }
}
