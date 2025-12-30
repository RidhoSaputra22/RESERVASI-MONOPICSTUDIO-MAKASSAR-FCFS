<?php

use Livewire\Volt\Component;

new class extends Component {
    //
};

?>

<div>
    {{-- @include('layouts.navbar') --}}

    {{-- Content --}}
    @livewire('guest.components.banner')
    @livewire('guest.components.tagline-1')
    @livewire('guest.components.tagline-2')
    @livewire('guest.components.about-us')
    @livewire('guest.components.services')
    @livewire('guest.components.faq')


    {{-- End Content --}}

    @include('layouts.footter')
</div>
