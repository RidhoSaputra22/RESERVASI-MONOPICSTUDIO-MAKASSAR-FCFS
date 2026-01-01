<?php

use App\Models\Package;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use App\Enums\BookingStatus;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //
    public Package $package;


    public ?string $booking_date = null;
    public ?string $booking_time = null;
    public ?string $notes = null;
    public string $selectedPaymentMethod = '';


    #[On('date-time-selected')]
    public function setDateTime($data)
    {
        $this->booking_date = $data['date'];
        $this->booking_time = $data['time'];
    }

    public function setPaymentMethod(string $method): void
    {
        $this->selectedPaymentMethod = $method;
    }

    public function submitForm(): void
    {
        if (! auth()->check()) {
            session()->flash('error', 'Silakan login terlebih dahulu untuk melakukan reservasi.');
            $this->redirectRoute('login');
            return;
        }

        $this->validate([

            'booking_date' => 'required|date_format:Y-m-d',
            'booking_time' => 'required|date_format:H:i',
        ], [

            'booking_date.required' => 'Tanggal reservasi wajib diisi.',
            'booking_time.required' => 'Waktu reservasi wajib diisi.',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $customer = Auth::user();

                $tz = 'Asia/Makassar';
                $scheduledAt = Carbon::parse("{$this->booking_date} {$this->booking_time}", $tz);

                Booking::create([
                    'customer_id' => $customer->id,
                    'package_id' => $this->package->id,
                    'scheduled_at' => $scheduledAt,
                    'status' => BookingStatus::Pending,
                ]);
            });

            session()->flash('success', 'Reservasi berhasil dibuat. Kami akan menghubungi Anda untuk konfirmasi.');
            $this->reset(['booking_date', 'booking_time', 'notes', 'selectedPaymentMethod']);
            $this->dispatch('booking-created');
        } catch (\Throwable $e) {
            report($e);
            $this->addError('form', 'Terjadi kesalahan saat menyimpan reservasi. Silakan coba lagi.');
        }


    }

    public function with()
    {
        //
        return [
            //
            'availablePaymentMethods' => [
                ['value' => 'bank_transfer', 'label' => 'Transfer Bank'],
                ['value' => 'e_wallet', 'label' => 'E-Wallet'],
            ]
        ];
    }


}; ?>

<div>
    <div wire:loading.class="opacity-50 cursor-not-allowed" class="space-y-5">
        @if (session()->has('success'))
            <div class="p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-3 border border-red-200 bg-red-50 rounded-md text-sm font-light text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->has('form'))
            <div class="p-3 border border-red-200 bg-red-50 rounded-md text-sm font-light text-red-700">
                {{ $errors->first('form') }}
            </div>
        @endif

        <div class="space-y-2">
            <h1 class="text-4xl font-bold">Booking Sekarang</h1>
            <p class="text-sm font-light">Silakan isi formulir di bawah untuk melakukan pemesanan.</p>
        </div>

        @guest
            <div class="p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
                Silakan login terlebih dahulu untuk mengisi formulir reservasi.
                <a href="{{ route('login') }}" class="underline">Login</a>
            </div>
        @endguest

        @auth
            <div class="space-y-4">
                @component('components.form.input', [
                'wireModel' => 'name',
                'label' => 'Nama Lengkap',
                'type' => 'text',
                'required' => true,
                'disabled' => true,

                ])

                @endcomponent
                @component('components.form.input', [
                'wireModel' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,
                'disabled' => true,

                ])

                @endcomponent
                @component('components.form.input', [
                'wireModel' => 'phone',
                'label' => 'Nomor Telepon',
                'type' => 'text',
                'required' => true,
                'disabled' => true,

                ])

                @endcomponent
            </div>
            <div>
                <h1 class="font-light pb-2">Pilih Tanggal & Waktu Reservasi</h1>
                @livewire('guest.booking.components.booking-callendar', ['package' => $package])
                @if ($errors->has('booking_date') || $errors->has('booking_time'))
                    <p class="text-sm font-light text-red-500">
                        Silakan pilih tanggal dan waktu reservasi.
                    </p>
                @endif
            </div>

            <div class="space-y-4">
                <h1 class="font-light ">Metode Pembayaran</h1>

                <div class="flex gap-4">
                    @foreach($availablePaymentMethods as $method)
                    <div wire:click="setPaymentMethod('{{ $method['value'] }}')"
                        class="px-2 py-3 border text-sm font-light border-gray-300 rounded-md cursor-pointer  flex items-center gap-3 {{ $selectedPaymentMethod === $method['value'] ? 'bg-primary text-white' : 'hover:border-primary hover:bg-gray-50' }}">
                        {{ $method['label'] }}
                    </div>
                    @endforeach
                </div>
            </div>
            <div>
                @component('components.form.button', [
                    'label' => 'Submit',
                    'wireClick' => 'submitForm',
                    'wireLoadingClass' => 'opacity-50',
                    'class' => 'w-full py-3 bg-primary text-white rounded-md hover:bg-primary-dark',
                    ])

                @endcomponent
            </div>
        @endauth
    </div>

</div>
