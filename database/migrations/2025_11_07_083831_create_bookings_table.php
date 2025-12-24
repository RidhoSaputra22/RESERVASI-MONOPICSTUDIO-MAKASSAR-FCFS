<?php

use Carbon\Carbon;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('photographer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('studio_id')->nullable()->constrained()->onDelete('set null');
            $table->dateTime('scheduled_at')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('code')->unique()->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        DB::table('bookings')->insert([
            [
                'code' => 'BOOK-' . now()->format('Ymd') . '-001',
                'customer_id' => 1,
                'package_id' => 1,
                'photographer_id' => 1,
                'studio_id' => 1,
                'scheduled_at' => Carbon::now()->addDays(1)->setTime(10, 0),
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BOOK-' . now()->format('Ymd') . '-002',
                'customer_id' => 2,
                'package_id' => 2,
                'photographer_id' => 2,
                'studio_id' => 2,
                'scheduled_at' => Carbon::now()->addDays(2)->setTime(14, 0),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BOOK-' . now()->format('Ymd') . '-003',
                'customer_id' => 3,
                'package_id' => 3,
                'photographer_id' => 3,
                'studio_id' => 3,
                'scheduled_at' => Carbon::now()->addDays(3)->setTime(9, 30),
                'status' => 'completed',
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
        Schema::dropIfExists('bookings');
    }
};
