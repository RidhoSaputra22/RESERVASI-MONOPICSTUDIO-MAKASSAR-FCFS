<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?string $selectedBookingStatus = null;

    public ?int $rescheduleBookingId = null;

    public bool $isRescheduleOpen = false;

    public ?string $reschedule_date = null;

    public ?string $reschedule_time = null;

    #[\Livewire\Attributes\On('date-time-selected')]
    public function setRescheduleDateTime($data): void
    {
        $this->reschedule_date = is_array($data) ? ($data['date'] ?? null) : null;
        $this->reschedule_time = is_array($data) ? ($data['time'] ?? null) : null;
    }

    public function openReschedule(int $bookingId): void
    {
        $this->rescheduleBookingId = $bookingId;
        $this->isRescheduleOpen = true;
        $this->reset(['reschedule_date', 'reschedule_time']);
        $this->dispatch('reset-booking-calendar');
    }

    public function closeReschedule(): void
    {
        $this->isRescheduleOpen = false;
        $this->rescheduleBookingId = null;
        $this->reset(['reschedule_date', 'reschedule_time']);
        $this->dispatch('reset-booking-calendar');
    }

    public function applyReschedule(): void
    {
        $userId = Auth::id();
        if (! $userId) {
            $this->redirectRoute('user.login');

            return;
        }

        $this->validate([
            'rescheduleBookingId' => 'required|integer',
            'reschedule_date' => 'required|date_format:Y-m-d',
            'reschedule_time' => 'required|date_format:H:i',
        ], [
            'reschedule_date.required' => 'Tanggal wajib dipilih.',
            'reschedule_time.required' => 'Jam wajib dipilih.',
        ]);

        try {
            $tz = 'Asia/Makassar';
            $scheduledAt = Carbon::parse("{$this->reschedule_date} {$this->reschedule_time}", $tz);

            $service = new ReservationService;
            $result = $service->rescheduleBookingByUser(
                bookingId: (int) $this->rescheduleBookingId,
                userId: (int) $userId,
                newScheduledAt: $scheduledAt->toDateTimeString(),
            );

            if (($result['ok'] ?? false) === true) {
                session()->flash('success', 'Jadwal booking berhasil diubah.');
                $this->closeReschedule();

                return;
            }

            $message = (string) ($result['message'] ?? 'Gagal mengubah jadwal.');
            session()->flash('error', $message);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Terjadi kesalahan saat menjadwal ulang.');
        }
    }

    public function cancelBooking(int $bookingId): void
    {
        $userId = Auth::id();
        if (! $userId) {
            $this->redirectRoute('user.login');

            return;
        }

        try {
            $service = new ReservationService;
            $result = $service->cancelBookingByUser(
                bookingId: $bookingId,
                userId: (int) $userId,
            );

            if (($result['ok'] ?? false) === true) {
                session()->flash('success', 'Booking berhasil dibatalkan.');
                if ($this->rescheduleBookingId === $bookingId) {
                    $this->closeReschedule();
                }

                return;
            }

            $message = (string) ($result['message'] ?? 'Gagal membatalkan booking.');
            session()->flash('error', $message);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Terjadi kesalahan saat membatalkan booking.');
        }
    }

    public function with(): array
    {
        $userId = Auth::id();

        if (! $userId) {
            $this->redirectRoute('user.login');

            return [];
        }

        $availableBookingStatus = BookingStatus::asArray();

        $bookings = Booking::query()
            ->with(['package', 'photographer', 'studio'])
            ->where('user_id', $userId)
            ->when($this->selectedBookingStatus && $this->selectedBookingStatus !== 'all', function ($query) {
                $query->where('status', $this->selectedBookingStatus);
            })
            ->latest()
            ->paginate(10);

        $activeRescheduleBooking = null;
        if ($this->rescheduleBookingId) {
            $activeRescheduleBooking = Booking::query()
                ->with(['package'])
                ->where('user_id', $userId)
                ->find($this->rescheduleBookingId);
        }

        return [
            'bookings' => $bookings,
            'availableBookingStatus' => $availableBookingStatus,
            'activeRescheduleBooking' => $activeRescheduleBooking,
        ];
    }
}; ?>


<div class="p-6 rounded-xl ">
    @if (session()->has('success'))
    <div class="mb-4 p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-4 p-3 border border-red-200 bg-red-50 rounded-md text-sm font-light text-red-700">
        {{ session('error') }}
    </div>
    @endif

    <div class="flex">
        <div class="mb-6 flex-1">
            <h2 class="text-xl font-semibold">Riwayat Pesanan</h2>
            <p class="text-sm text-gray-500">Daftar pesanan yang pernah Anda buat.</p>
        </div>
        <div>
            @component('components.form.select', [
            'label' => '',
            'wireModel' => 'selectedBookingStatus',
            'options' => $availableBookingStatus,
            'default' => ['label' => 'Semua Status', 'value' => 'all']
            ])

            @endcomponent
        </div>
    </div>


    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none">
        {{ $bookings->links() }}
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-3 pr-4">Kode Booking</th>
                    <th class="py-3 pr-4">Paket</th>
                    <th class="py-3 pr-4">Jadwal</th>
                    <th class="py-3 pr-4">Studio</th>
                    <th class="py-3 pr-4">Fotografer</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3 pr-4">Konfirmasi</th>
                    <th class="py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                <tr class="border-b">
                    <td class="py-3 pr-4 font-medium">{{ $booking->code ?? '-' }}</td>
                    <td class="py-3 pr-4">{{ $booking->package?->name ?? '-' }}</td>
                    <td class="py-3 pr-4">{{ optional($booking->scheduled_at)->format('d M Y H:i') ?? '-' }}</td>
                    <td class="py-3 pr-4">{{ $booking->studio?->name ?? '-' }}</td>
                    <td class="py-3 pr-4">{{ $booking->photographer?->name ?? '-' }}</td>
                    <td class="py-3 pr-4">
                        @php
                        $status = $booking->status;
                        $label = $status?->getLabel() ?? ($status?->value ?? '-');
                        $color = match ($status?->value) {
                        'pending' => 'bg-amber-100 text-amber-700',
                        'confirmed' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700',
                        };
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="py-3 pr-4">
                        @if ($booking->readiness_confirmed_at)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            ✓ Dikonfirmasi
                        </span>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $booking->readiness_confirmed_at->format('d M Y H:i') }}
                        </div>
                        @else
                        <span class="text-xs text-gray-400">Belum dikonfirmasi</span>
                        @endif
                    </td>
                    <td class="py-3">
                        <div class="flex items-center gap-2">
                            @php
                            $isWithin30Minutes = false;
                            $diffInMinutes = null;
                            if ($booking->scheduled_at) {
                                $now = \Carbon\Carbon::now();
                                $diffInMinutes = $now->diffInMinutes($booking->scheduled_at, false);
                                // Jika dalam range 30-60 menit sebelum jadwal
                                $isWithin30Minutes = $diffInMinutes >= 0 && $diffInMinutes <= 60;
                            }
                            @endphp

                            @if ($booking->status === BookingStatus::Confirmed && optional($booking->scheduled_at)->isFuture())
                                @if ($isWithin30Minutes && !$booking->readiness_confirmed_at)
                                    {{-- Tombol Konfirmasi Kesiapan --}}
                                    <a href="{{ route('booking.confirm-readiness', ['bookingId' => $booking->id]) }}"
                                        class="px-3 py-2 bg-green-600 rounded-sm text-white text-xs hover:bg-green-700">
                                        ✓ Konfirmasi Kesiapan
                                    </a>
                                @elseif ($isWithin30Minutes && $booking->readiness_confirmed_at)
                                    {{-- Sudah dikonfirmasi --}}
                                    <span class="text-xs text-green-600">Siap ✓</span>
                                @else
                                    {{-- Tombol normal: Jadwal Ulang & Batalkan --}}
                                    <button wire:click="openReschedule({{ (int) $booking->id }})"
                                        class="px-3 py-2 bg-gray-900 rounded-sm text-white text-xs">
                                        Jadwal Ulang
                                    </button>
                                    <button wire:click="cancelBooking({{ (int) $booking->id }})"
                                        class="px-3 py-2 bg-red-600 rounded-sm text-white text-xs">
                                        Batalkan
                                    </button>
                                @endif
                            @elseif($booking->status === BookingStatus::Pending)
                                <button wire:click="cancelBooking({{ (int) $booking->id }})"
                                    class="px-3 py-2 bg-gray-900 rounded-sm text-white text-xs">
                                    Konfirmasi
                                </button>
                                <button wire:click="cancelBooking({{ (int) $booking->id }})"
                                    class="px-3 py-2 bg-red-600 rounded-sm text-white text-xs">
                                    Batalkan
                                </button>
                            @else
                                <span class="text-xs text-gray-500">-</span>
                            @endif
                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="8" class="py-6 text-center text-gray-500">Belum ada riwayat pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>

    @if ($isRescheduleOpen && $activeRescheduleBooking)
    @component('components.modal', [
    'maxWidth' => 'max-w-4xl',
    ])
    @slot('title')
    Jadwal Ulang Booking {{ $activeRescheduleBooking->code ?? '' }}
    @endslot

    <div class="space-y-4">
        <div class="text-sm text-gray-600">
            Pilih tanggal & jam baru, lalu klik simpan.
        </div>

        @livewire('guest.booking.components.booking-callendar', [
        'package' => $activeRescheduleBooking->package,
        'excludeBookingId' => $activeRescheduleBooking->id,
        ])

        @if ($errors->has('reschedule_date') || $errors->has('reschedule_time'))
        <p class="text-sm font-light text-red-500">Silakan pilih tanggal dan jam.</p>
        @endif

        <div class="flex justify-end gap-2">
            <button wire:click="closeReschedule" class="px-4 py-2 border rounded">
                Batal
            </button>
            <button wire:click="applyReschedule" class="px-4 py-2 bg-primary text-white rounded">
                Simpan Jadwal
            </button>
        </div>
    </div>
    @endcomponent
    @endif
</div>