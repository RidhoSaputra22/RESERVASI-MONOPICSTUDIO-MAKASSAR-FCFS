<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Volt::mount([
            // Ikuti config('livewire.view_path') yang di project ini menunjuk ke resources/views
            // sehingga Volt components bisa disimpan langsung di resources/views/...
            config('livewire.view_path', resource_path('views')),
            resource_path('views/pages'),
        ]);
    }
}
