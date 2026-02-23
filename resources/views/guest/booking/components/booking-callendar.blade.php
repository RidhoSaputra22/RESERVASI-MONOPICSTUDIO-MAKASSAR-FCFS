<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Package;
use App\Services\ReservationService;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component
{
    public Package $package;

    public ?int $excludeBookingId = null;

    protected $listeners = [
        'select-date' => 'selectDate',
        'reset-booking-calendar' => 'resetBookingCalendar',
    ];

    public bool $isOpen = false;

    public string $step = 'calendar'; // calendar | time

    public ?string $selectedDate = null;

    public ?string $selectedTime = null;

    public function open(): void
    {
        $this->isOpen = true;
        $this->step = 'calendar';
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->step = 'time';
    }

    public function backToCalendar(): void
    {
        $this->step = 'calendar';
        $this->selectedDate = null;
        $this->selectedTime = null;
    }

    public function selectTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    public function proceed(): void
    {
        $this->dispatch('date-time-selected', [
            'date' => $this->selectedDate,
            'time' => $this->selectedTime,
        ]);

        $this->close();
    }

    public function resetBookingCalendar(): void
    {
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->step = 'calendar';
    }

    public function with()
    {
        $events = Booking::query()
            ->with(['user', 'package'])
            ->whereNotNull('scheduled_at')
            ->where('status', '!=', BookingStatus::Cancelled)
            ->get()
            ->map(function ($booking) {
                $start = $booking->scheduled_at;
                $end = $booking->scheduled_at->copy()->addMinutes($booking->package->duration_minutes);

                return [
                    'title' => Str::limit($booking->user->name, 5),
                    'start' => $start->toDateTimeString(),
                    'end' => $end->toDateTimeString(),
                ];
            })->toArray();

        $availableSlotTime = ReservationService::getAvailableTimeSlots(
            date: $this->selectedDate ?? now()->format('Y-m-d'),
            // date: '2026-01-01',
            durationMinutes: $this->package->duration_minutes,
            excludeBookingId: $this->excludeBookingId,
        );

        // dd($availableSlotTime, $events);

        return [
            'events' => $events,
            'availableSlotTime' => $availableSlotTime,
        ];
    }
};
?>

<div x-data="{
        isOpen: @entangle('isOpen'),
        step: 'calendar',
        selectedDate: null,
        calendar: null,
    }" x-init="$watch('isOpen', (value) => {
        if (value) {
            step = 'calendar';
            selectedDate = null;
        }
    })">
    <style>
    .fc-day-disabled,
    .fc-day-disabled * {
        pointer-events: none;
    }

    .fc-day-disabled {
        opacity: 0.45;
        background: #f3f4f6;
        /* gray-100 */
    }
    </style>
    <!-- Trigger -->
    @if($selectedDate && $selectedTime && $isOpen === false)
    <div wire:loading.class="opacity-50" wire:click="open"
        class="px-4 py-4 border border-gray-300 border-dashed rounded-md cursor-pointer hover:border-primary hover:bg-gray-50 text-sm font-light">

        Tanggal & Waktu Terpilih: <strong>{{ $selectedDate }} {{ $selectedTime }}</strong>
    </div>
    @else
    <div wire:loading.class="opacity-50" wire:click="open"
        class="px-4 py-4 border border-gray-300 border-dashed rounded-md cursor-pointer hover:border-primary hover:bg-gray-50 text-sm font-light">

        Klik untuk memilih
    </div>
    @endif

    <!-- Modal -->
    @component('components.modal', [
    'maxWidth' => 'max-w-4xl',
    ])
    @slot('title')
    Jadwal Reservasi
    @endslot


    <!-- FullCalendar -->
    <div wire:ignore x-cloak x-show="step === 'calendar'" x-init="
        let  todayStart = new Date();
        todayStart.setHours(0, 0, 0, 0);

        calendar = new FullCalendar.Calendar($refs.calendar, {
                locale: 'id',

                initialView: 'dayGridMonth',
                height: 520,


                firstDay: 1, // Senin
                dayMaxEvents: true,

                validRange: {
                    start: todayStart,
                },

                dayCellDidMount: function (arg) {
                    // Disable yesterday and earlier (visual + no interaction)
                    var cellDate = new Date(arg.date);
                    cellDate.setHours(0, 0, 0, 0);
                    if (cellDate < todayStart) {
                        arg.el.classList.add('fc-day-disabled');
                    }
                },

                dateClick(info) {
                    var clicked = new Date(info.date);
                    clicked.setHours(0, 0, 0, 0);
                    if (clicked < todayStart) {
                        return;
                    }
                    selectedDate = info.dateStr;
                    step = 'time';
                    Livewire.dispatch('select-date', {
                        date: info.dateStr
                    });
                },

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },

                buttonText: {
                    today: 'Hari ini',
                    month: 'Bulan',
                    week: 'Minggu',
                    day: 'Hari'
                },

                titleFormat: {
                    year: 'numeric',
                    month: 'long'
                },

                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },

                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },

                events: @js($events),

                eventOverlap: false,

                eventDisplay: 'block',

                nowIndicator: true,

                selectable: false,

        });

        " x-effect="
            if (isOpen) {
                $nextTick(() => {
                    calendar.render();
                    calendar.updateSize();
                });
            }
        ">
        <div x-ref="calendar"></div>
    </div>


    {{-- STEP 2: TIME PICKER --}}
    <div wire:loading.class="opacity-50" x-cloak x-show="step === 'time'" class="space-y-4 h-4xl">
        <div>
            <h1>Pilih Jam Untuk Hari {{ $selectedDate !== null ? date('d F Y', strtotime($selectedDate)) : 'xx-xx-xx' }}
            </h1>
        </div>
        <div class="grid grid-cols-6 gap-3">
            @foreach ($availableSlotTime as $slot)
            <button wire:click="selectTime('{{ $slot['time'] }}')"
                class="border rounded-lg px-4 py-2 {{ $selectedTime === $slot['time'] ? 'bg-primary text-white ' : '' }} {{ ! $slot['available'] ? 'bg-gray-200 text-gray-400 cursor-not-allowed!' : 'hover:bg-primary hover:text-white' }}"
                {{ ! $slot['available'] ? 'disabled' : '' }}>
                {{ $slot['time'] }}
            </button>
            @endforeach
        </div>

        <div class="flex justify-between mt-6">
            <button @click="step = 'calendar'; selectedDate = null; $wire.backToCalendar()"
                class="text-sm text-gray-600">
                ← Kembali
            </button>

            <button wire:click="proceed" class="px-4 py-2 bg-primary text-white rounded">
                Lanjutkan
            </button>
        </div>
    </div>

    @endcomponent
</div>
