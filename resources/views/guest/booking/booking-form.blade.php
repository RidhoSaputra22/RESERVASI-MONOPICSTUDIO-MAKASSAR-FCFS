<?php

use Carbon\Carbon;
use App\Models\Package;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //
    public Package $package;

    public string $name = '';
    public string $email = '';
    public string $phone = '';

    public ?string $booking_date = null;
    public ?string $booking_time = null;


    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $this->name ?: ($user->name ?? '');
            $this->email = $this->email ?: ($user->email ?? '');
            $this->phone = $this->phone ?: ($user->hp ?? '');
        }
    }

    #[On('date-time-selected')]
    public function setDateTime($data)
    {
        $this->booking_date = $data['date'];
        $this->booking_time = $data['time'];
    }



    public function clearData(): void
    {
        $this->reset([
            'booking_date',
            'booking_time',
            'selectedPaymentMethod',
        ]);

        $this->dispatch('reset-booking-calendar');
    }

    #[On('booking-success')]
    public function handleBookingSuccess()
    {
        session()->flash('success', 'Reservasi berhasil dibuat!');
    }



    public function submitForm(): void
    {
        if (! Auth::check()) {
            session()->flash('error', 'Silakan login terlebih dahulu untuk melakukan reservasi.');
            $this->redirectRoute('login');
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'booking_date' => 'required|date_format:Y-m-d',
            'booking_time' => 'required|date_format:H:i',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'booking_date.required' => 'Tanggal reservasi wajib diisi.',
            'booking_time.required' => 'Waktu reservasi wajib diisi.',
        ]);

        try {
            $user = Auth::user();

            $tz = 'Asia/Makassar';
            $scheduledAt = Carbon::parse("{$this->booking_date} {$this->booking_time}", $tz);

            $reservationService = new \App\Services\ReservationService();
            $result = $reservationService->createReservation([
                'user_id' => $user->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'package_id' => $this->package->id,
                'scheduled_at' => $scheduledAt,

            ]);

            if (is_array($result) && isset($result['snap_token'])) {
                $this->dispatch('open-midtrans-snap', snapToken: $result['snap_token']);
            }

            $this->clearData();
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

@push('scripts')
    @once
        @php
            $isProduction = (bool) config('services.midtrans.is_production', false);
            $clientKey = (string) config('services.midtrans.client_key', '');
            $snapBaseUrl = $isProduction
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';
        @endphp

        @if ($clientKey !== '')
            <script src="{{ $snapBaseUrl }}" data-client-key="{{ $clientKey }}"></script>
        @endif

        <script>
        (function () {
            function waitForSnap(maxMs) {
                return new Promise(function (resolve, reject) {
                    var start = Date.now();
                    (function tick() {
                        if (window.snap && typeof window.snap.pay === 'function') {
                            return resolve();
                        }
                        if (Date.now() - start > maxMs) {
                            return reject(new Error('Midtrans Snap is not available'));
                        }
                        setTimeout(tick, 100);
                    })();
                });
            }

            document.addEventListener('open-midtrans-snap', function (event) {
                var detail = event && event.detail ? event.detail : {};
                var snapToken = detail.snapToken;
                if (!snapToken) return;

                waitForSnap(5000)
                    .then(function () {
                        window.snap.pay(snapToken, {
                            onSuccess: function (result) {
                                Livewire.dispatch('payment-success', { payload: result || {} });
                            },
                            onPending: function () {
                                window.location.reload();
                            },
                            onError: function () {
                                alert('Pembayaran gagal diproses. Silakan coba lagi.');
                            },
                            onClose: function () {
                                // user closed the popup
                            }
                        });
                    })
                    .catch(function (err) {
                        console.error(err);
                        alert('Gagal memuat pembayaran. Silakan refresh halaman dan coba lagi.');
                    });
            });
        })();
        </script>
    @endonce
@endpush
