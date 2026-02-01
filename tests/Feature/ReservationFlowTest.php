<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Http\Controllers\BookingController;
use App\Jobs\SendWhatsAppBookingNotification;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Photographer;
use App\Models\SesiFoto;
use App\Models\Studio;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    private string $tz = 'Asia/Makassar';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => $this->tz]);
        date_default_timezone_set($this->tz);
        Carbon::setLocale('id');

        // Define test-only routes (the app routes currently don't expose these endpoints).
        if (! Route::has('test.bookings.store')) {
            Route::post('/__tests/bookings', [BookingController::class, 'store'])->name('test.bookings.store');
        }
        if (! Route::has('test.midtrans.callback')) {
            Route::post('/__tests/midtrans/callback', [BookingController::class, 'callback'])->name('test.midtrans.callback');
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_reservation_creates_booking_and_returns_snap_token(): void
    {
        Notification::fake();

        $package = Package::factory()->create([
            'price' => 150000,
            'duration_minutes' => 60,
        ]);

        Mockery::mock('alias:Midtrans\\Snap')
            ->shouldReceive('getSnapToken')
            ->once()
            ->andReturn('dummy-snap-token');

        $response = $this->postJson('/__tests/bookings', [
            'name' => 'Test User',
            'email' => null,
            'phone' => '08123456789',
            'package_id' => $package->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('snap_token', 'dummy-snap-token')
            ->assertJsonPath('booking.status', BookingStatus::Pending->value);

        $this->assertDatabaseCount('bookings', 1);

        $booking = Booking::query()->firstOrFail();
        $this->assertSame('dummy-snap-token', $booking->snap_token);
        $this->assertSame(BookingStatus::Pending->value, $booking->status->value);

        $user = User::query()->findOrFail($booking->user_id);
        $this->assertSame('Test User', $user->name);

        Notification::assertSentTo(
            $user,
            GenericDatabaseNotification::class,
            function (GenericDatabaseNotification $notification, array $channels) use ($user) {
                $data = $notification->toArray($user);

                return in_array('database', $channels, true)
                    && ($data['kind'] ?? null) === 'booking_created'
                    && isset($data['booking_id']);
            }
        );
    }

    public function test_midtrans_callback_settlement_with_schedule_confirms_booking(): void
    {
        Notification::fake();
        Queue::fake();

        Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00', $this->tz));

        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);
        SesiFoto::query()->create(['name' => '2', 'session_time' => '10:00:00']);

        $studio = Studio::factory()->create();
        $photographer = Photographer::factory()->create(['is_available' => true]);

        $package = Package::factory()->create(['duration_minutes' => 60, 'price' => 150000]);
        $user = User::factory()->create();

        // Booking with scheduled_at should be confirmed
        $scheduledAt = Carbon::parse('2026-01-02 09:00:00', $this->tz);

        Booking::withoutEvents(function () use ($user, $package, $scheduledAt) {
            Booking::query()->create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => $scheduledAt,
                'snap_token' => 'dummy',
                'status' => BookingStatus::Pending->value,
                'code' => 'ORD-TEST-1',
            ]);
        });

        $response = $this->postJson('/__tests/midtrans/callback', [
            'order_id' => 'ORD-TEST-1',
            'transaction_status' => 'settlement',
        ]);

        $response->assertOk();

        $booking = Booking::query()->where('code', 'ORD-TEST-1')->firstOrFail();
        $booking->refresh();

        $this->assertSame(BookingStatus::Confirmed->value, $booking->status->value);
        $this->assertNotNull($booking->scheduled_at);
        $this->assertNotNull($booking->studio_id);
        $this->assertNotNull($booking->photographer_id);

        $this->assertSame('2026-01-02 09:00', $booking->scheduled_at->copy()->setTimezone($this->tz)->format('Y-m-d H:i'));
        $this->assertSame($studio->id, $booking->studio_id);
        $this->assertSame($photographer->id, $booking->photographer_id);

        Notification::assertSentTo($user, GenericDatabaseNotification::class);
        Notification::assertSentTo($photographer, GenericDatabaseNotification::class);
        Queue::assertPushed(SendWhatsAppBookingNotification::class);
    }

    public function test_midtrans_callback_cancel_marks_booking_cancelled(): void
    {
        Notification::fake();

        $package = Package::factory()->create(['duration_minutes' => 60, 'price' => 150000]);
        $user = User::factory()->create();

        Booking::withoutEvents(function () use ($user, $package) {
            Booking::query()->create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => null,
                'snap_token' => 'dummy',
                'status' => BookingStatus::Pending->value,
                'code' => 'ORD-TEST-2',
            ]);
        });

        $response = $this->postJson('/__tests/midtrans/callback', [
            'order_id' => 'ORD-TEST-2',
            'transaction_status' => 'cancel',
        ]);

        $response->assertOk();

        $booking = Booking::query()->where('code', 'ORD-TEST-2')->firstOrFail();
        $this->assertSame(BookingStatus::Cancelled->value, $booking->status->value);

        Notification::assertSentTo($user, GenericDatabaseNotification::class);
    }

    /**
     * Test race condition: two users book the same slot simultaneously.
     * First user should succeed, second user's booking should be cancelled.
     */
    public function test_race_condition_same_slot_second_booking_is_cancelled(): void
    {
        Notification::fake();
        Queue::fake();

        Carbon::setTestNow(Carbon::parse('2026-02-01 00:00:00', $this->tz));

        // Setup sesi foto
        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);
        SesiFoto::query()->create(['name' => '2', 'session_time' => '10:00:00']);

        // Only 1 studio and 1 photographer available
        $studio = Studio::factory()->create();
        $photographer = Photographer::factory()->create(['is_available' => true]);

        $package = Package::factory()->create(['duration_minutes' => 60, 'price' => 150000]);

        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        $scheduledAt = Carbon::parse('2026-02-07 09:00:00', $this->tz);

        // Create two pending bookings for the SAME time slot
        Booking::withoutEvents(function () use ($user1, $user2, $package, $scheduledAt) {
            Booking::query()->create([
                'user_id' => $user1->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => $scheduledAt,
                'snap_token' => 'dummy-1',
                'status' => BookingStatus::Pending->value,
                'code' => 'ORD-RACE-1',
            ]);

            Booking::query()->create([
                'user_id' => $user2->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => $scheduledAt,
                'snap_token' => 'dummy-2',
                'status' => BookingStatus::Pending->value,
                'code' => 'ORD-RACE-2',
            ]);
        });

        // First user pays - should succeed
        $response1 = $this->postJson('/__tests/midtrans/callback', [
            'order_id' => 'ORD-RACE-1',
            'transaction_status' => 'settlement',
        ]);
        $response1->assertOk();

        $booking1 = Booking::query()->where('code', 'ORD-RACE-1')->firstOrFail();
        $booking1->refresh();

        // First booking should be confirmed with the requested slot
        $this->assertSame(BookingStatus::Confirmed->value, $booking1->status->value);
        $this->assertSame('2026-02-07 09:00', $booking1->scheduled_at->copy()->setTimezone($this->tz)->format('Y-m-d H:i'));
        $this->assertSame($studio->id, $booking1->studio_id);
        $this->assertSame($photographer->id, $booking1->photographer_id);

        // Second user pays - should be CANCELLED because slot is taken
        $response2 = $this->postJson('/__tests/midtrans/callback', [
            'order_id' => 'ORD-RACE-2',
            'transaction_status' => 'settlement',
        ]);
        $response2->assertOk();

        $booking2 = Booking::query()->where('code', 'ORD-RACE-2')->firstOrFail();
        $booking2->refresh();

        // Second booking should be CANCELLED (not rescheduled to another date)
        $this->assertSame(BookingStatus::Cancelled->value, $booking2->status->value);

        // Verify user2 received cancellation notification
        Notification::assertSentTo($user2, GenericDatabaseNotification::class);
    }

    /**
     * Test booking without scheduled_at should be cancelled
     */
    public function test_booking_without_schedule_is_cancelled(): void
    {
        Notification::fake();
        Queue::fake();

        Carbon::setTestNow(Carbon::parse('2026-02-01 00:00:00', $this->tz));

        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);

        Studio::factory()->create();
        Photographer::factory()->create(['is_available' => true]);

        $package = Package::factory()->create(['duration_minutes' => 60, 'price' => 150000]);
        $user = User::factory()->create();

        // Create booking WITHOUT scheduled_at
        Booking::withoutEvents(function () use ($user, $package) {
            Booking::query()->create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => null, // No schedule selected
                'snap_token' => 'dummy',
                'status' => BookingStatus::Pending->value,
                'code' => 'ORD-NO-SCHED',
            ]);
        });

        $response = $this->postJson('/__tests/midtrans/callback', [
            'order_id' => 'ORD-NO-SCHED',
            'transaction_status' => 'settlement',
        ]);

        $response->assertOk();

        $booking = Booking::query()->where('code', 'ORD-NO-SCHED')->firstOrFail();
        $booking->refresh();

        // Booking should be cancelled
        $this->assertSame(BookingStatus::Cancelled->value, $booking->status->value);

        // Verify user received notification about missing schedule
        Notification::assertSentTo($user, GenericDatabaseNotification::class);
    }

    /**
     * Test that when multiple slots are available, second booking can still succeed
     */
    public function test_multiple_resources_allow_concurrent_bookings(): void
    {
        Notification::fake();
        Queue::fake();

        Carbon::setTestNow(Carbon::parse('2026-02-01 00:00:00', $this->tz));

        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);

        // Create 2 studios and 2 photographers
        $studio1 = Studio::factory()->create();
        $studio2 = Studio::factory()->create();
        $photographer1 = Photographer::factory()->create(['is_available' => true]);
        $photographer2 = Photographer::factory()->create(['is_available' => true]);

        $package = Package::factory()->create(['duration_minutes' => 60, 'price' => 150000]);

        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        $scheduledAt = Carbon::parse('2026-02-07 09:00:00', $this->tz);

        // Create two bookings for the SAME time slot
        Booking::withoutEvents(function () use ($user1, $user2, $package, $scheduledAt) {
            Booking::query()->create([
                'user_id' => $user1->id,
                'package_id' => $package->id,
                'scheduled_at' => $scheduledAt,
                'snap_token' => 'dummy-1',
                'status' => BookingStatus::Pending->value,
                'code' => 'ORD-MULTI-1',
            ]);

            Booking::query()->create([
                'user_id' => $user2->id,
                'package_id' => $package->id,
                'scheduled_at' => $scheduledAt,
                'snap_token' => 'dummy-2',
                'status' => BookingStatus::Pending->value,
                'code' => 'ORD-MULTI-2',
            ]);
        });

        // First user pays
        $this->postJson('/__tests/midtrans/callback', [
            'order_id' => 'ORD-MULTI-1',
            'transaction_status' => 'settlement',
        ]);

        // Second user pays
        $this->postJson('/__tests/midtrans/callback', [
            'order_id' => 'ORD-MULTI-2',
            'transaction_status' => 'settlement',
        ]);

        $booking1 = Booking::query()->where('code', 'ORD-MULTI-1')->firstOrFail();
        $booking2 = Booking::query()->where('code', 'ORD-MULTI-2')->firstOrFail();

        // BOTH bookings should be confirmed (different photographer/studio assigned)
        $this->assertSame(BookingStatus::Confirmed->value, $booking1->status->value);
        $this->assertSame(BookingStatus::Confirmed->value, $booking2->status->value);

        // They should have different photographers
        $this->assertNotEquals($booking1->photographer_id, $booking2->photographer_id);

        // They should have different studios
        $this->assertNotEquals($booking1->studio_id, $booking2->studio_id);
    }
}
