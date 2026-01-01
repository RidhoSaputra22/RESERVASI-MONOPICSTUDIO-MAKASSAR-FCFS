<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Photographer;
use App\Models\SesiFoto;
use App\Models\Studio;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationServiceGetAvailableTimeSlotsTest extends TestCase
{
    use RefreshDatabase;

    private string $tz = 'Asia/Makassar';

    private function shouldLog(): bool
    {
        return (bool) env('TEST_LOG', false);
    }

    private function logLine(string $message, array $context = []): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $payload = [
            'ts' => Carbon::now($this->tz)->format('Y-m-d H:i:s'),
            'test' => $this->name(),
            'msg' => $message,
        ];

        if ($context !== []) {
            $payload['context'] = $context;
        }

        fwrite(STDERR, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan interpretasi scheduled_at konsisten dengan timezone yang dipakai method.
        config(['app.timezone' => $this->tz]);
        date_default_timezone_set($this->tz);

        Carbon::setLocale('id');

        $this->logLine('setup', [
            'app.timezone' => (string) config('app.timezone'),
        ]);
    }

    public function test_returns_empty_array_when_date_is_empty(): void
    {
        $package = Package::factory()->create(['duration_minutes' => 60]);

        $this->logLine('created package', [
            'package_id' => $package->id,
            'duration_minutes' => $package->duration_minutes,
        ]);

        $resultNull = ReservationService::getAvailableTimeSlots(date: null, durationMinutes: 60);
        $this->logLine('result for null date', ['result' => $resultNull]);
        $this->assertSame([], $resultNull);

        $resultEmpty = ReservationService::getAvailableTimeSlots(date: '', durationMinutes: 60);
        $this->logLine('result for empty date', ['result' => $resultEmpty]);
        $this->assertSame([], $resultEmpty);
    }

    public function test_returns_empty_array_when_no_session_slots_exist(): void
    {
        SesiFoto::query()->delete();
        $package = Package::factory()->create(['duration_minutes' => 60]);

        $this->logLine('sesi_fotos cleared', [
            'sesi_fotos_count' => SesiFoto::query()->count(),
            'package_id' => $package->id,
        ]);

        $result = ReservationService::getAvailableTimeSlots(date: '2026-01-02', durationMinutes: 60);

        $this->logLine('result', ['result' => $result]);

        $this->assertSame([], $result);
    }

    public function test_all_slots_available_when_no_bookings_overlap(): void
    {
        SesiFoto::query()->delete();
        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);
        SesiFoto::query()->create(['name' => '2', 'session_time' => '10:00:00']);
        SesiFoto::query()->create(['name' => '3', 'session_time' => '11:00:00']);

        Studio::factory()->create();
        Photographer::factory()->create(['is_available' => true]);

        $package = Package::factory()->create(['duration_minutes' => 60]);

        $this->logLine('seeded slots', [
            'slots' => SesiFoto::query()->orderBy('session_time')->pluck('session_time')->all(),
            'package_id' => $package->id,
        ]);

        $result = ReservationService::getAvailableTimeSlots(date: '2026-01-02', durationMinutes: 60);

        $this->logLine('result', ['result' => $result]);

        $this->assertSame(
            [
                ['time' => '09:00', 'available' => true],
                ['time' => '10:00', 'available' => true],
                ['time' => '11:00', 'available' => true],
            ],
            $result,
        );
    }

    public function test_overlapping_booking_marks_slots_unavailable_using_package_duration(): void
    {
        SesiFoto::query()->delete();
        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);
        SesiFoto::query()->create(['name' => '2', 'session_time' => '10:00:00']);
        SesiFoto::query()->create(['name' => '3', 'session_time' => '11:00:00']);

        // Resource hanya 1 studio + 1 photographer -> jika ada booking overlap, slot jadi tidak available.
        Studio::factory()->create();
        Photographer::factory()->create(['is_available' => true]);

        $date = '2026-01-02';

        $package = Package::factory()->create([
            'duration_minutes' => 120, // booking akan memblokir 2 slot (10:00-12:00)
        ]);

        $otherPackage = Package::factory()->create(['duration_minutes' => 120]);

        $user = User::factory()->create();

        $this->logLine('prepared data', [
            'date' => $date,
            'package_id' => $package->id,
            'package_duration' => $package->duration_minutes,
            'other_package_id' => $otherPackage->id,
            'user_id' => $user->id,
        ]);

        // Booking untuk package utama: start 10:00, durasi 120 -> 10:00 & 11:00 tidak available.
        Booking::withoutEvents(function () use ($user, $package, $date) {
            Booking::query()->create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => Carbon::parse("$date 10:00", $this->tz),
                'snap_token' => null,
                'status' => 'confirmed',
                'code' => 'TEST-CODE-1',
            ]);
        });

        $this->logLine('created booking for main package', [
            'scheduled_at' => Carbon::parse("$date 10:00", $this->tz)->toDateTimeString(),
        ]);

        // Booking package lain pada 09:00 juga harus memblokir slot pada tanggal yang sama.
        Booking::withoutEvents(function () use ($user, $otherPackage, $date) {
            Booking::query()->create([
                'user_id' => $user->id,
                'package_id' => $otherPackage->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => Carbon::parse("$date 09:00", $this->tz),
                'snap_token' => null,
                'status' => 'confirmed',
                'code' => 'TEST-CODE-2',
            ]);
        });

        $this->logLine('created booking for other package', [
            'scheduled_at' => Carbon::parse("$date 09:00", $this->tz)->toDateTimeString(),
        ]);

        $result = ReservationService::getAvailableTimeSlots(date: $date, durationMinutes: 60);

        $this->logLine('result', ['result' => $result]);

        $this->assertSame(
            [
                ['time' => '09:00', 'available' => false],
                ['time' => '10:00', 'available' => false],
                ['time' => '11:00', 'available' => false],
            ],
            $result,
        );
    }

    public function test_can_filter_conflicts_by_studio_id(): void
    {
        SesiFoto::query()->delete();
        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);
        SesiFoto::query()->create(['name' => '2', 'session_time' => '10:00:00']);

        $date = '2026-01-02';

        $package = Package::factory()->create(['duration_minutes' => 60]);
        $user = User::factory()->create();

        $studioA = Studio::factory()->create();
        $studioB = Studio::factory()->create();

        Photographer::factory()->create(['is_available' => true]);

        Booking::withoutEvents(function () use ($user, $package, $date, $studioB) {
            Booking::query()->create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => $studioB->id,
                'scheduled_at' => Carbon::parse("$date 09:00", $this->tz),
                'snap_token' => null,
                'status' => 'confirmed',
                'code' => 'TEST-CODE-STUDIO-B',
            ]);
        });

        $result = ReservationService::getAvailableTimeSlots(date: $date, durationMinutes: 60, studioId: $studioA->id);

        $this->logLine('result filtered by studio', [
            'studio_id' => $studioA->id,
            'result' => $result,
        ]);

        $this->assertSame(
            [
                ['time' => '09:00', 'available' => true],
                ['time' => '10:00', 'available' => true],
            ],
            $result,
        );
    }

    public function test_slot_still_available_when_resources_remaining(): void
    {
        SesiFoto::query()->delete();
        SesiFoto::query()->create(['name' => '1', 'session_time' => '09:00:00']);

        // Total resources: 2 photographer + 2 studio.
        Studio::factory()->count(2)->create();
        Photographer::factory()->count(2)->create(['is_available' => true]);

        $date = '2026-01-02';
        $package = Package::factory()->create(['duration_minutes' => 60]);
        $user = User::factory()->create();

        Booking::withoutEvents(function () use ($user, $package, $date) {
            Booking::query()->create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'photographer_id' => null,
                'studio_id' => null,
                'scheduled_at' => Carbon::parse("$date 09:00", $this->tz),
                'snap_token' => null,
                'status' => 'confirmed',
                'code' => 'TEST-CODE-CAPACITY-1',
            ]);
        });

        $result = ReservationService::getAvailableTimeSlots(date: $date, durationMinutes: 60);

        $this->logLine('result capacity', [
            'result' => $result,
        ]);

        $this->assertSame(
            [
                ['time' => '09:00', 'available' => true],
            ],
            $result,
        );
    }
}
