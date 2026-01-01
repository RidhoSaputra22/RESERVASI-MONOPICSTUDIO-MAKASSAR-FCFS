<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Volt::route('/', 'guest.home.welcome')->name('welcome');
Volt::route('/booking/{slug}', 'guest.booking.booking')->name('guest.booking');
Volt::route('/about-us', 'guest.booking.about')->name('about-us');
Volt::route('/gallery', 'guest.booking.gallery')->name('gallery');


Volt::route('/paket', 'guest.paket.paket')->name('paket');



Volt::route('/user/dashboard', 'user.dashboard')->name('user.dashboard');


Volt::route('/user/login', 'auth.login')->middleware('guest')->name('user.login');
Volt::route('/login', 'auth.login')->middleware('guest')->name('login');
Volt::route('/user/register', 'auth.regist')->middleware('guest')->name('user.register');


Route::post('/user/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('user.login');
})->middleware('auth')->name('user.logout');
