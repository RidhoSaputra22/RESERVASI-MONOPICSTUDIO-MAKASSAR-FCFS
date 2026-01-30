<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?string $selectedStatus = null;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        if (!Auth::guard('photographer')->check()) {
            $this->redirectRoute('photographer.login');
            return;
        }
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedDate(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $photographer = Auth::guard('photographer')->user();

        if (!$photographer) {
            $this->redirectRoute('photographer.login');
            return [];
        }

        $availableStatuses = BookingStatus::asArray();

        // Query bookings for this photographer
        $bookingsQuery = Booking::query()
            ->with(['package', 'user', 'studio'])
            ->where('photographer_id', $photographer->id)
            ->when($this->selectedStatus && $this->selectedStatus !== 'all', function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->selectedDate, function ($query) {
                $query->whereDate('scheduled_at', $this->selectedDate);
            })
            ->orderBy('scheduled_at', 'asc');

        // Get upcoming bookings (confirmed and scheduled in the future)
        $upcomingBookings = Booking::query()
            ->with(['package', 'user', 'studio'])
            ->where('photographer_id', $photographer->id)
            ->where('status', BookingStatus::Confirmed)
            ->where('scheduled_at', '>=', Carbon::now('Asia/Makassar'))
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        // Get today's bookings
        $todayBookings = Booking::query()
            ->with(['package', 'user', 'studio'])
            ->where('photographer_id', $photographer->id)
            ->whereDate('scheduled_at', Carbon::today('Asia/Makassar'))
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // Statistics
        $totalBookings = Booking::where('photographer_id', $photographer->id)->count();
        $completedBookings = Booking::where('photographer_id', $photographer->id)
            ->where('status', BookingStatus::Completed)
            ->count();
        $pendingBookings = Booking::where('photographer_id', $photographer->id)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Pending])
            ->where('scheduled_at', '>=', Carbon::now('Asia/Makassar'))
            ->count();

        return [
            'photographer' => $photographer,
            'bookings' => $bookingsQuery->paginate(10),
            'upcomingBookings' => $upcomingBookings,
            'todayBookings' => $todayBookings,
            'availableStatuses' => $availableStatuses,
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
            'pendingBookings' => $pendingBookings,
        ];
    }
}; ?>

<div>
    @livewire('layouts.navbar')

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Photographer</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Selamat datang, <span class="font-medium">{{ $photographer->name }}</span>
                </p>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Booking</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalBookings }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Selesai</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $completedBookings }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-amber-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Menunggu</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $pendingBookings }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content - Schedule Table --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <h2 class="text-lg font-semibold text-gray-900">Jadwal Sesi Foto</h2>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input
                                        type="date"
                                        wire:model.live="selectedDate"
                                        class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                    >
                                    <select
                                        wire:model.live="selectedStatus"
                                        class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                    >
                                        <option value="all">Semua Status</option>
                                        @foreach ($availableStatuses as $status)
                                            <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto" wire:loading.class="opacity-50">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="text-left py-3 px-4 font-medium text-gray-600">Kode</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-600">Jadwal</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-600">Paket</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-600">Customer</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-600">Studio</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($bookings as $booking)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 font-medium">{{ $booking->code ?? '-' }}</td>
                                            <td class="py-3 px-4">
                                                @if ($booking->scheduled_at)
                                                    <div class="text-gray-900">{{ $booking->scheduled_at->format('d M Y') }}</div>
                                                    <div class="text-gray-500 text-xs">{{ $booking->scheduled_at->format('H:i') }} WITA</div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">{{ $booking->package?->name ?? '-' }}</td>
                                            <td class="py-3 px-4">
                                                <div>{{ $booking->user?->name ?? '-' }}</div>
                                                <div class="text-gray-500 text-xs">{{ $booking->user?->hp ?? '' }}</div>
                                            </td>
                                            <td class="py-3 px-4">{{ $booking->studio?->name ?? '-' }}</td>
                                            <td class="py-3 px-4">
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
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-8 text-center text-gray-500">
                                                Belum ada jadwal sesi foto.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 border-t border-gray-200">
                            {{ $bookings->links() }}
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Today's Schedule --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-900">Jadwal Hari Ini</h3>
                            <p class="text-sm text-gray-500">{{ Carbon::now('Asia/Makassar')->format('d F Y') }}</p>
                        </div>
                        <div class="p-4">
                            @forelse ($todayBookings as $booking)
                                <div class="flex items-start gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-100' : '' }}">
                                    <div class="flex-shrink-0 w-12 text-center">
                                        <div class="text-lg font-bold text-primary">{{ $booking->scheduled_at?->format('H:i') }}</div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $booking->package?->name ?? '-' }}</p>
                                        <p class="text-sm text-gray-500">{{ $booking->user?->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">{{ $booking->studio?->name ?? '-' }}</p>
                                    </div>
                                    @php
                                        $status = $booking->status;
                                        $dotColor = match ($status?->value) {
                                            'confirmed' => 'bg-blue-500',
                                            'completed' => 'bg-green-500',
                                            'cancelled' => 'bg-red-500',
                                            default => 'bg-amber-500',
                                        };
                                    @endphp
                                    <span class="flex-shrink-0 w-2 h-2 mt-2 rounded-full {{ $dotColor }}"></span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">Tidak ada jadwal hari ini.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Upcoming Schedule --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-900">Jadwal Mendatang</h3>
                        </div>
                        <div class="p-4">
                            @forelse ($upcomingBookings as $booking)
                                <div class="flex items-start gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-100' : '' }}">
                                    <div class="flex-shrink-0 text-center">
                                        <div class="text-xs text-gray-500">{{ $booking->scheduled_at?->format('d M') }}</div>
                                        <div class="text-sm font-bold text-primary">{{ $booking->scheduled_at?->format('H:i') }}</div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $booking->package?->name ?? '-' }}</p>
                                        <p class="text-sm text-gray-500">{{ $booking->user?->name ?? '-' }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">Tidak ada jadwal mendatang.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footter')
</div>
