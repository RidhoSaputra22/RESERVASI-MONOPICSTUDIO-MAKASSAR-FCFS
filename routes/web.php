<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'guest.home.welcome')->name('welcome');
Volt::route('/booking', 'guest.booking.booking')->name('guest.booking');
Volt::route('/about-us', 'guest.booking.booking')->name('about-us');
Volt::route('/gallery', 'guest.booking.booking')->name('gallery');


Volt::route('/paket', 'guest.paket.paket')->name('paket');






Volt::route('/produk','guest.booking.booking')->name('produk.cari');
Volt::route('/produk/detail/{slug}', 'guest.booking.booking')->name('produk.detail');
Volt::route('/tentang', 'guest.booking.booking')->name('tentang');
Volt::route('/keranjang', 'guest.booking.booking')->name('cart.index');

Volt::route('/user/dashboard', 'guest.booking.booking')->name('user.dashboard');


Volt::route('/user/login', 'guest.booking.booking')->name('user.login');
Volt::route('/login', 'guest.booking.booking')->name('login');
Volt::route('/user/logout', 'guest.booking.booking')->name('user.logout');
Volt::route('/user/register', 'guest.booking.booking')->name('user.register');
