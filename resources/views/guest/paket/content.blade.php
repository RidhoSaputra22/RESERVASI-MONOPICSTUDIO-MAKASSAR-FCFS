<?php

use App\Models\Package;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component {
    //

    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public ?string $selectedDuration = null;
    public ?string $selectedHarga = null;
    public ?string $selectedSortBy = null;


    public function mount()
{
    if (request()->has('category')) {
        $this->selectedCategorySlug = request('category');
    }
}

    /**
     * Auto reset pagination ketika filter berubah
     */
    public function updated($property)
    {
        if (in_array($property, [
            'search',
            'selectedCategorySlug',
            'selectedHarga',
            'selectedSortBy',
        ])) {
            $this->resetPage();
        }
    }

    /**
     * Query produk (AMAN & TER-GROUPING)
     */
    public function getProducts()
    {
        $query = Package::query();


        // Filter Duration
        if ($this->selectedDuration) {
            $query->where('duration_minutes', $this->selectedDuration);
        }

        // Filter harga
        if ($this->selectedHarga === 'low_to_high') {
            $query->orderBy('price', 'asc');
        } elseif ($this->selectedHarga === 'high_to_low') {
            $query->orderBy('price', 'desc');
        }

        // Sorting
        if ($this->selectedSortBy === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($this->selectedSortBy === 'oldest') {
            $query->orderBy('created_at', 'asc');
        }

        // Search (GROUPED)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(10);
    }

    public function with()
    {

        $availableDurations = Package::select('duration_minutes')
            ->distinct()
            ->orderBy('duration_minutes', 'asc')
            ->get()
            ->pluck('duration_minutes');

        return [
            'products'   => $this->getProducts(),
            'durations' => $availableDurations,
        ];
    }

}; ?>


<section class="p-12 min-h-screen " id="#paginated-posts">
    <div class="flex gap-24">
        {{-- FILTER --}}
        <div class="flex-1 bg-white rounded-2xl">
            <h1 class="text-xl font-semibold mb-6">Filter Produk</h1>

            <div class="space-y-6">
                @component('components.form.select', [
                'label' => 'Durasi',
                'wireModel' => 'selectedDuration',
                'default' => [
                'label' => 'Semua Durasi',
                'value' => '',
                ],
                'options' => $durations->map(fn ($d) => [
                'label' => $d,
                'value' => $d,
                ]),
                ]) @endcomponent

                @component('components.form.select', [
                'label' => 'Harga',
                'wireModel' => 'selectedHarga',
                'options' => [
                ['label' => 'Semua Harga', 'value' => ''],
                ['label' => 'Rendah ke Tinggi', 'value' => 'low_to_high'],
                ['label' => 'Tinggi ke Rendah', 'value' => 'high_to_low'],
                ],
                ]) @endcomponent

                @component('components.form.select', [
                'label' => 'Urutkan',
                'wireModel' => 'selectedSortBy',
                'options' => [
                ['label' => 'Terbaru', 'value' => 'newest'],
                ['label' => 'Terlama', 'value' => 'oldest'],
                ],
                ]) @endcomponent
            </div>
        </div>
        <div class="flex-5 space-y-14 ">
            <div>
                <input type="text" wire:model.live.debounce.500ms="search" class="border rounded px-4 py-2 w-full"
                    placeholder="Masukkan nama produk...">
            </div>
            {{ $products->links(data: ['scrollTo' => '#paginated-posts']) }}

            <div class="grid grid-cols-4 gap-10 " wire:loading.class="opacity-60 bg-white animate-pulse">
                @forelse ($products as $product)
                <a href="{{ route('produk.detail', ['slug' => $product->slug]) }}" class="">
                    <div class="relative">
                        <img src="{{ Storage::url($product->productImages->first->image->image ?? 'products/product_placeholder.jpg') }}"
                            alt="" class="rounded-xl w-full h-60 object-cover">
                        <div
                            class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-sm font-medium text-white">
                            {{ $product->category->name }}</div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                            {{ $product->name }}
                        </h1>
                        <h1 class="text-lg font-semibold mt-2">Rp.
                            {{ number_format($product->price, 0, ',', ',') }}
                        </h1>
                        <div class="flex gap-2 text-primary items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                            </svg>
                            <p class="truncate text-md/loose w-1/2">Toko
                                {{ $product->vendor->store_name  ?? 'Nama Toko' }}
                            </p>

                        </div>
                    </div>
                </a>
                @empty
                <p class="text-center col-span-4 text-lg font-light">Produk tidak ditemukan</p>
                @endforelse
            </div>

        </div>
    </div>
</section>
