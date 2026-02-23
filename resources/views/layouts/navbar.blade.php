<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    #[On('booking-updated-nav')]
    public function refreshBookingHint(): void
    {
        // no-op; triggers re-render
    }

    #[On('user-photo-updated')]
    public function refreshUserPhoto(): void
    {
        // no-op; triggers re-render
    }
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

        @if(!auth('photographer')->check())
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
        @endif
    </div>

    <div class="flex flex-1 justify-end ">
        <ul class="flex gap-5 ">
            @if (auth()->check())
            <li>
                @livewire('layouts.components.notification')
            </li>

            <li class="ml-3 relative">
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
                            @php
                            $navUser = auth()->user()?->fresh();
                            @endphp
                            @if (!empty($navUser?->photo))
                            <img src="{{ asset('storage/' . $navUser->photo) }}" alt="Foto Profil"
                                class="size-13 aspect-square object-cover rounded-full ">
                            @else
                            <div class="size-13 aspect-square rounded-full border bg-gray-50"></div>
                            @endif
                            <div>
                                <h1 class="text-lg/tight font-semibold">{{ $navUser?->name ?? '' }}</h1>
                                <span class="text-sm/tight font-light text-gray-500">{{ $navUser?->email ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class=" ">
                        <a href="{{ route('user.dashboard') }}"
                            class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                            Lihat Profil
                        </a>
                        <a href="{{ route('user.dashboard', ['tab' => 'history']) }}"
                            class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                            Lihat History Booking
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

                <div class="absolute -bottom-12 -right-1.5 w-52" x-data="{ show: false }"
                    x-on:booking-updated-nav.window="show = true; setTimeout(() => show = false, 3000)" x-cloak
                    x-show="show" x-transition.duration.500ms>
                    <div class="relative">
                        <span
                            class="absolute -top-3 z-20 right-2 inline-block w-0 h-0 border-solid border-t-0 border-r-[9px] border-l-[9px] border-b-[17.3px] border-l-transparent border-r-transparent border-t-transparent border-b-white">
                        </span>
                        <span
                            class="absolute -top-3 -z-10 right-2  inline-block w-0 h-0 border-solid border-t-0 border-r-[9px] border-l-[9px] border-b-[17.3px] border-l-transparent border-r-transparent border-t-transparent border-b-gray-600">
                        </span>
                        <div
                            class=" text-xs text-center px-3 py-2 bg-white border border-gray-300 rounded-md shadow-md">
                            Lihat Booking Kamu disini
                        </div>
                    </div>

                </div>
            </li>



            @elseif (auth('photographer')->check())
            <li class="ml-3 relative">
                @component('components.dropdown', [
                'align' => 'right',
                'width' => 'min-w-sm',
                ])
                @slot('trigger')
                Hello, {{ auth('photographer')->user()->name }}
                @endslot
                @slot('content')
                <div>
                    <div class="border-b border-gray-400">
                        <div class="flex gap-3 items-center mb-3 mx-4 my-3">
                            <div
                                class="size-13 aspect-square rounded-full border bg-gray-50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-lg/tight font-semibold">{{ auth('photographer')->user()->name }}</h1>
                                <span
                                    class="text-sm/tight font-light text-gray-500">{{ auth('photographer')->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('photographer.dashboard') }}"
                            class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('photographer.logout') }}">
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
            @if(!auth()->check() && !auth('photographer')->check())
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