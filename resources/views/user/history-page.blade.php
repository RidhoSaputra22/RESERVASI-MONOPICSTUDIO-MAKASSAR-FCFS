<?php

use App\Models\Booking;
use App\Enums\BookingStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public ?string $selectedBookingStatus = null;

    public function with(): array
    {
        $userId = Auth::id();

        if (!$userId) {
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



        return [
            'bookings' => $bookings,
            'availableBookingStatus' => $availableBookingStatus,
        ];
    }

}; ?>


<div class="p-6 rounded-xl ">
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
                    <th class="py-3">Status</th>
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
                    <td class="py-3">{{ $booking->status?->value ?? '-' }}</td>
                </tr>

                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-500">Belum ada riwayat pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
</div>
