<?php

use App\Models\Package;
use Livewire\Volt\Component;

new class extends Component
{
    //
    public bool $readyToLoad = false;

    public function loadInitialData()
    {
        $this->readyToLoad = true;
    }

    public function with()
    {
        if (! $this->readyToLoad) {
            return [];
        }

        $paketRecomended = Package::inRandomOrder()
            ->take(5)
            ->get();

        return [
            'paketRecomended' => $paketRecomended,
        ];
    }
}; ?>

<div class=" px-12 py-24 space-y-10 " wire:init="loadInitialData">

    <div class="">
        <h1 class="text-4xl/normal font-semibold">Paket Foto </h1>
        <p class="text-lg font-light">Cari paket foto disini</p>
    </div>

    @if (!$readyToLoad)
    <div class="grid grid-cols-5 gap-5 overflow-y-auto">
        @for ($i = 0; $i < 5; $i++) <div>
            <div class="rounded-xl bg-gray-200 aspect-square animate-pulse"></div>
            <div class="mt-4 space-y-2">
                <div class="h-5 w-4/5 bg-gray-200 rounded animate-pulse"></div>
                <div class="h-5 w-1/2 bg-gray-200 rounded animate-pulse"></div>
                <div class="h-5 w-3/5 bg-gray-200 rounded animate-pulse"></div>
            </div>
    </div>
    @endfor
</div>
@else
<div class="grid grid-cols-5 gap-5 overflow-y-auto">
    @forelse ($paketRecomended as $package)
    <a href="{{ route('guest.booking', ['slug' => $package->slug]) }}" class="">
        <div class="relative">
            <img src="{{ Storage::url($package->photo ?? 'packages/package_placeholder.jpg') }}" alt=""
                class="rounded-xl w-full h-60 object-cover">
            <div class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-sm font-medium text-white">
                {{ $package->category->name }}</div>
        </div>
        <div class="mt-4 space-y-2">
            <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                {{ $package->name }}
            </h1>
            <h1 class="text-lg font-semibold mt-2">Rp.
                {{ number_format($package->price, 0, ',', ',') }}
            </h1>
            <div class="flex gap-2 text-primary items-center">
                @component('components.icon.clock')

                @endcomponent

                <div>{{ $package->duration_minutes }} menit</div>


            </div>
        </div>
    </a>
    @empty
    <p class="text-center col-span-4 text-lg font-light">Produk tidak ditemukan</p>
    @endforelse
</div>
<div class="flex justify-end">
    <a href="{{ route('paket') }}"
        class=" px-3 py-2  rounded-sm  outline-offset-1 bg-primary text-white hover:bg-secondary hover:outline hover:outline-2 outline-primary text-center   ">
        Lihat Paket Lainnya >>
    </a>
</div>



@endif
</div>
