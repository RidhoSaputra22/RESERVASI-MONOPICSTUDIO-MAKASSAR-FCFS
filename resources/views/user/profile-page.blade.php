<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $hp = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirectRoute('user.login');
            return;
        }

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->hp = (string) $user->hp;
    }

    public function save(): void
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'hp' => ['required', 'string', 'max:255', Rule::unique('users', 'hp')->ignore($userId)],
        ]);

        User::query()->whereKey($userId)->update($validated);

        session()->flash('success', 'Profil berhasil diperbarui.');
    }
}; ?>


<div class="p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Profil Saya</h2>
        <p class="text-sm text-gray-500">Perbarui informasi akun Anda.</p>
    </div>

    @if (session()->has('success'))
        <div class="p-3 rounded-sm border border-green-200 bg-green-50 text-green-700 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-5" wire:loading.class="opacity-50 pointer-events-none">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @component('components.form.input', [
                'label' => 'Nama',
                'type' => 'text',
                'wireModel' => 'name',
                'placeholder' => 'Masukkan nama',
                'required' => true,
            ])
            @endcomponent

            @component('components.form.input', [
                'label' => 'Email',
                'type' => 'email',
                'wireModel' => 'email',
                'placeholder' => 'Masukkan email',
                'required' => true,
            ])
            @endcomponent

            @component('components.form.input', [
                'label' => 'No. HP',
                'type' => 'text',
                'wireModel' => 'hp',
                'placeholder' => 'Masukkan nomor HP',
                'required' => true,
            ])
            @endcomponent
        </div>

        <div>
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="bg-primary text-white px-6 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
