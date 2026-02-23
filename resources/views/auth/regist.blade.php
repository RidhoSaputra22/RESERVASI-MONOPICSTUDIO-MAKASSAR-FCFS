<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $hp = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('user.dashboard'), navigate: false);
        }
    }

    public function register()
    {
        // Validasi input
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'hp' => ['required', 'string', 'max:15', 'unique:users,hp'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        // Buat user baru
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'hp' => $data['hp'],
            'password' => $data['password'],
            'role' => UserRole::Customer,
        ]);

        // Kirim event Registered untuk mengirim email verifikasi
        event(new Registered($user));

        // Login user secara otomatis setelah registrasi
        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended();
    }
}; ?>

<div>
    <div class="h-screen flex items-center justify-center px-6 py-12 bg-primary">
        <div class="max-w-xl mx-auto w-full bg-white p-8 rounded-sm shadow-lg">
            <div class="mb-6 space-y-2">
                <h1 class="text-2xl font-semibold">Register</h1>
                <p class="text-sm font-light">Silahkan masukan data diri anda</p>
            </div>
            <form wire:submit="register" class="space-y-4 ">
                @csrf

                @component('components.form.input', [
                'wireModel' => 'name',
                'label' => 'Nama',
                'type' => 'text',
                'required' => true,
                ])
                @endcomponent

                @component('components.form.input', [
                'wireModel' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,
                ])
                @endcomponent

                @component('components.form.input', [
                'wireModel' => 'hp',
                'label' => 'No. HP',
                'type' => 'text',
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

                @component('components.form.input', [
                'wireModel' => 'password_confirmation',
                'label' => 'Konfirmasi Password',
                'type' => 'password',
                'required' => true,
                ])
                @endcomponent

                <div class="flex items-center justify-between">
                    <a href="{{ route('user.login') }}" class="text-sm text-primary hover:underline">
                        Sudah punya akun?
                    </a>
                </div>

                @component('components.form.button', [
                'label' => 'Daftar',
                'class' => 'w-full bg-primary text-white rounded-md',
                ])
                @endcomponent
            </form>


        </div>
    </div>


</div>
