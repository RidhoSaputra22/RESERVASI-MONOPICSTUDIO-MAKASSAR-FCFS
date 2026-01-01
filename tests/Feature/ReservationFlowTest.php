<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Http\Controllers\BookingController;
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

    public function test_midtrans_callback_settlement_schedules_booking_fcfs(): void
    {
        Notification::fake();

        Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00', $this->tz));

        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);
        SesiFoto::query()->create(['name' => '2', 'session_time' => '10:00:00']);

        $studio = Studio::factory()->create();
        $photographer = Photographer::factory()->create(['is_available' => true]);

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
}
