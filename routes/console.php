<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule reminder 30 menit sebelum booking dimulai
// Berjalan setiap 5 menit untuk mengecek booking yang akan dimulai
Schedule::command('bookings:send-reminders')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Batalkan otomatis booking pending yang waktu jadwalnya sudah terlewati
// Berjalan setiap menit agar pembatalan segera terjadi begitu waktu terlewati
Schedule::command('bookings:cancel-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();
