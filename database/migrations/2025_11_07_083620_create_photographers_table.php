<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photographers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        DB::table('photographers')->insert([
            [
                'name' => 'Fauzan',
                'email' => 'fauzan@me.com',
                'phone' => '08123456789',
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ridho Saputra',
                'email' => 'ridho@studio.com',
                'phone' => '08124567890',
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maya Fitriani',
                'email' => 'maya@studio.com',
                'phone' => '08214567891',
                'is_available' => false,
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
        Schema::dropIfExists('photographers');
    }
};