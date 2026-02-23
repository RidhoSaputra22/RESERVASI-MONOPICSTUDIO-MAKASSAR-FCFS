<?php

use App\Models\Package;
use Livewire\Volt\Component;

new class extends Component
{
    //

    public function with()
    {
        // Ambil 3 paket terbaru untuk ditampilkan di halaman utama
        $paket = Package::take(3)->get();

        return [
            'paket' => $paket,

        ];
    }
};

?>

<div>
    {{-- @include('layouts.navbar') --}}

    {{-- Content --}}
    @include('guest.home.components.banner')
    @livewire('guest.home.components.services-1')
    @include('guest.home.components.tagline-1')
    @include('guest.home.components.tagline-2')
    @include('guest.home.components.about-us')
    @include('guest.home.components.services')
    @include('guest.home.components.faq')


    {{-- End Content --}}

    @include('layouts.footter')
</div>
