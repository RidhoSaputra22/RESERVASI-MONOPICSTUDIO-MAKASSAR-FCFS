<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Package $package;

    public int $rating = 5;
    public string $comment = '';

    public function submitReview(): void
    {
        if (! Auth::check()) {
            session()->flash('error', 'Silakan login terlebih dahulu untuk memberi ulasan.');
            $this->redirectRoute('login');
            return;
        }

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Rating wajib diisi.',
            'rating.min' => 'Rating minimal 1.',
            'rating.max' => 'Rating maksimal 5.',
            'comment.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $userId = (int) Auth::id();

        $eligibleBooking = Booking::query()
            ->where('user_id', $userId)
            ->where('package_id', $this->package->id)
            ->where('status', BookingStatus::Completed->value)
            ->whereDoesntHave('review')
            ->latest('scheduled_at')
            ->first();

        if (! $eligibleBooking) {
            session()->flash('error', 'Anda belum memiliki booking selesai untuk paket ini, atau Anda sudah pernah memberi ulasan.');
            return;
        }

        Review::query()->create([
            'booking_id' => $eligibleBooking->id,
            'user_id' => $userId,
            'package_id' => $this->package->id,
            'rating' => $this->rating,
            'comment' => trim($this->comment) !== '' ? trim($this->comment) : null,
        ]);

        $this->reset(['rating', 'comment']);
        $this->rating = 5;

        session()->flash('success', 'Terima kasih! Ulasan Anda berhasil dikirim.');
    }

    public function with(): array
    {
        $reviews = Review::query()
            ->where('package_id', $this->package->id)
            ->with('user')
            ->latest()
            ->get();

        $ratingAvg = (float) ($reviews->avg('rating') ?? 0);
        $ratingCount = (int) $reviews->count();

        $ratingOptions = collect([5, 4, 3, 2, 1])
            ->map(fn (int $value) => ['value' => $value, 'label' => (string) $value])
            ->toArray();

        return [
            'reviews' => $reviews,
            'ratingAvg' => $ratingAvg,
            'ratingCount' => $ratingCount,
            'ratingOptions' => $ratingOptions,
        ];
    }
}; ?>

<div class="space-y-5">
    <div class="space-y-1">
        <h2 class="text-2xl font-semibold">Ulasan</h2>
        <p class="text-sm font-light">
            Rata-rata: {{ number_format($ratingAvg, 1) }} / 5 ({{ $ratingCount }} ulasan)
        </p>
    </div>

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

    <div class="p-4 border border-gray-200 rounded-lg space-y-4">
        <h3 class="text-lg font-semibold">Tulis Ulasan</h3>

        @guest
            <div class="p-3 border border-gray-200 bg-gray-50 rounded-md text-sm font-light">
                Silakan <a href="{{ route('login') }}" class="underline">login</a> untuk memberi ulasan.
            </div>
        @endguest

        @auth
            @component('components.form.select', [
                'label' => 'Rating',
                'wireModel' => 'rating',
                'options' => $ratingOptions,
                'class' => 'w-full',
            ])
            @endcomponent

            @error('rating')
                <p class="text-sm font-light text-red-500">{{ $message }}</p>
            @enderror

            @component('components.form.textarea', [
                'label' => 'Komentar (opsional)',
                'wireModel' => 'comment',
                'rows' => 4,
                'class' => 'w-full',
            ])
            @endcomponent

            @error('comment')
                <p class="text-sm font-light text-red-500">{{ $message }}</p>
            @enderror

            @component('components.form.button', [
                'label' => 'Kirim Ulasan',
                'wireClick' => 'submitReview',
                'wireLoadingClass' => 'opacity-50',
                'class' => 'px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark',
            ])
            @endcomponent
        @endauth
    </div>

    <div class="space-y-3">
        @forelse ($reviews as $review)
            <div class="p-4 border border-gray-200 rounded-lg space-y-2">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-semibold">
                        {{ $review->user?->name ?? 'User' }}
                    </div>
                    <div class="text-sm font-light text-gray-600">
                        {{ $review->created_at?->format('d-m-Y') }}
                    </div>
                </div>

                <div class="text-sm">
                    <span class="font-semibold">{{ $review->rating }}/5</span>
                    <span class="font-light text-gray-600">
                        {{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}
                    </span>
                </div>

                @if (! empty($review->comment))
                    <p class="text-sm font-light">{{ $review->comment }}</p>
                @else
                    <p class="text-sm font-light text-gray-500">(Tanpa komentar)</p>
                @endif
            </div>
        @empty
            <div class="p-4 border border-gray-200 rounded-lg text-sm font-light text-gray-600">
                Belum ada ulasan untuk paket ini.
            </div>
        @endforelse
    </div>
</div>
