<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // bisa Customer atau Photographer
            $table->string('type')->nullable(); // booking_created, reminder, dsb
            $table->text('message');
            $table->boolean('is_sent')->default(false);
            $table->timestamps();
        });

        DB::table('notifications')->insert([
            [
                'notifiable_type' => 'App\\Models\\Customer',
                'notifiable_id' => 1,
                'type' => 'booking_created',
                'message' => 'Terima kasih Andi, reservasi Anda telah dibuat.',
                'is_sent' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'notifiable_type' => 'App\\Models\\Customer',
                'notifiable_id' => 2,
                'type' => 'reminder',
                'message' => 'Jangan lupa sesi foto Anda besok pukul 14.00!',
                'is_sent' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'notifiable_type' => 'App\\Models\\Photographer',
                'notifiable_id' => 1,
                'type' => 'schedule_update',
                'message' => 'Jadwal foto untuk Andi telah dikonfirmasi.',
                'is_sent' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};