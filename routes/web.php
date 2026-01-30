<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'guest.home.welcome')->name('welcome');
Volt::route('/booking/{slug}', 'guest.booking.booking')->name('guest.booking');
Volt::route('/about-us', 'guest.home.about')->name('about-us');
Volt::route('/gallery', 'guest.booking.gallery')->name('gallery');

Volt::route('/paket', 'guest.paket.paket')->name('paket');

Volt::route('/user/dashboard', 'user.dashboard')->name('user.dashboard');

Volt::route('/user/login', 'auth.login')->middleware('guest')->name('user.login');
Volt::route('/login', 'auth.login')->middleware('guest')->name('login');
Volt::route('/user/register', 'auth.regist')->middleware('guest')->name('user.register');

// Laporan
Volt::route('laporan/booking', 'laporan.booking')->name('laporan.booking');

Route::post('/user/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('user.login');
})->middleware('auth')->name('user.logout');

// Photographer Routes
Volt::route('/photographer/login', 'photographer.login')->name('photographer.login');
Volt::route('/photographer/dashboard', 'photographer.dashboard')->name('photographer.dashboard');

Route::post('/photographer/logout', function () {
    Auth::guard('photographer')->logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('photographer.login');
})->name('photographer.logout');

Route::get('/test-email', function () {
    $user = User::firstOrCreate(
        [
            'email' => 'saputra22022@gmail.com',
        ], [
            'name' => 'Saputra',
            'hp' => '081344968521',
            'role' => UserRole::Customer,
            'password' => bcrypt('password'),
        ]);

    $user->notify(new GenericDatabaseNotification(
        message: 'This is a test email notification.',
        kind: 'info',
    ));

    return 'Email notification sent to '.$user->email;

});
