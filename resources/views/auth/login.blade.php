<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect($this->defaultRedirectUrl(), navigate: false);
        }
    }

    public function login()
    {
        // Validasi input
        $credentials = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.request()->ip());

        // Cek apakah sudah terlalu banyak percobaan login
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Coba login dengan guard default (user)
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $credentials['remember'])) {

            RateLimiter::clear($throttleKey);
            request()->session()->regenerate();

            return redirect()->intended();

        }

        // Coba login dengan guard photographer
        if (Auth::guard('photographer')->attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $credentials['remember'])) {
            RateLimiter::clear($throttleKey);
            request()->session()->regenerate();

            return redirect()->route('photographer.dashboard');
        }

        // Jika login gagal, catat percobaan login
        RateLimiter::hit($throttleKey, 60);


        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);

    }
}; ?>

<div>

    <div class="h-screen flex items-center justify-center px-6 py-12 bg-primary">
        <div class="max-w-md mx-auto w-full bg-white p-8 rounded-sm shadow-lg">
            <div class="mb-6 space-y-2">
                <h1 class="text-2xl font-semibold">Login</h1>
                <p class="text-sm font-light">Silahkan masukan email dan password anda</p>
            </div>
            <form wire:submit="login" class="space-y-4 ">
                @csrf
                @component('components.form.input', [
                'wireModel' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,
                ])

                @endcomponent

                @component('components.form.input', [
                'wireModel' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'required' => true,
                ])

                @endcomponent


                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="remember" class="rounded border-gray-300" />
                        <span class="text-sm">Ingat saya</span>
                    </label>

                    <a href="{{ route('user.register') }}" class="text-sm text-primary hover:underline">
                        Belum punya akun?
                    </a>
                </div>

                @component('components.form.button', [
                'label' => 'Masuk',
                'type' => 'submit',
                'class' => 'w-full bg-primary text-white rounded-md',
                ])

                @endcomponent
            </form>


        </div>
    </div>


</div>
