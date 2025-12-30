<?php

namespace Database\Factories;

use App\Notifications\GenericDatabaseNotification;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Photographer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'notifiable_type' => Customer::class,
            'notifiable_id' => Customer::factory(),
            'type' => GenericDatabaseNotification::class,
            'data' => [
                'kind' => fake()->randomElement(['booking_created', 'reminder', 'schedule_update']),
                'message' => fake()->sentence(),
            ],
            'read_at' => fake()->optional(60)->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function forPhotographer(): static
    {
        return $this->state(fn () => [
            'notifiable_type' => Photographer::class,
            'notifiable_id' => Photographer::factory(),
        ]);
    }
}
