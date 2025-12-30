<?php

use Livewire\Volt\Component;

new class extends Component {
    //
};

?>

<div>
    {{-- @include('layouts.navbar') --}}

    {{-- Content --}}
    @livewire('guest.home.components.banner')
    @livewire('guest.home.components.tagline-1')
    @livewire('guest.home.components.tagline-2')
    @livewire('guest.home.components.about-us')
    @livewire('guest.home.components.services')
    @livewire('guest.home.components.faq')


    {{-- End Content --}}

    @include('layouts.footter')
</div>
