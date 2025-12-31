<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    @livewire('layouts.navbar')


    {{-- Content --}}
    @livewire('guest.paket.banner')
    @livewire('guest.paket.content')

    {{-- End Content --}}


    @livewire('layouts.footter')
</div>
