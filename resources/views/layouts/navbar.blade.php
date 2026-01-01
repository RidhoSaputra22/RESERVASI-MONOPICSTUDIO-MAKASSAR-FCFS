<?php

use Livewire\Volt\Component;

new class extends Component {
    //

    public int $cartCount = 0;

};

?>

@php
$navActive = function (string|array $patterns, string $active = 'text-primary ', string $inactive =
'hover:text-primary') {
return request()->routeIs($patterns) ? $active : $inactive;
};

$isActive = function (string|array $patterns): bool {
return request()->routeIs($patterns);
};
@endphp

<div class="h-20 flex items-center justify-between py-5 px-12 ">
    <div class="flex  flex-1">
        <div class="flex  gap-2">
            <!-- <img src="{{ asset('images/logo.jpg') }}" alt="" class="w-14 aspect-square"> -->
            <div class="">
                <h1 class="text-xl font-semibold ">Monopic Studio</h1>
            </div>
        </div>
    </div>
    <div class="flex-2 flex justify-center ">
        <ul class="flex gap-10   ">
            <li>
                <a href="{{ route('welcome') }}" class="block  hover:text-primary {{ $navActive('') }}">Beranda</a>
            </li>
            <li>
                <a href="{{ route('paket') }}" class="block  hover:text-primary {{ $navActive('paket*') }}">Cari
                    Paket</a>
            </li>
            <li>
                <a href="{{ route('about-us') }}" class="block  hover:text-primary {{ $navActive('about-us') }}">Tentang
                    Kami</a>
            </li>
        </ul>
    </div>

    <div class="flex flex-1 justify-end ">
        <ul class="flex gap-5 ">
            @if (auth()->check())

            <li class="ml-3">
                @component('components.dropdown', [
                'align' => 'right',
                'width' => 'min-w-sm',


                ])
                @slot('trigger')
                Hello, {{ auth()->user()->name }}
                @endslot
                @slot('content')
                <div>
                    <div class="border-b border-gray-400">
                        <div class="flex gap-3 items-center mb-3  mx-4 my-3">
                            <img src="{{ Storage::url(auth()->user()->foto ?? 'user-placeholder.png') }}" alt=""
                                class="size-13 aspect-square object-cover rounded-full ">
                            <div>
                                <h1 class="text-lg/tight font-semibold">{{ auth()->user()->name }}</h1>
                                <span class="text-sm/tight font-light text-gray-500">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class=" ">
                        <a href="{{ route('user.dashboard') }}"
                            class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                            Lihat Profil
                        </a>
                        <form method="POST" action="{{ route('user.logout') }}">
                            @csrf
                            <button type="submit"
                                class="text-sm font-medium block w-full text-left px-4 py-4 hover:bg-gray-100">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                @endslot
                @endcomponent
            </li>



            @endif
            @if(!auth()->check())
            <li>
                <a href="{{ route('user.login') }}" class="hover:text-primary">Masuk</a>
            </li>
            <li>
                <a href="{{ route('user.register') }}" class="hover:text-primary">Daftar</a>
            </li>
            @endif
        </ul>
    </div>
</div>
