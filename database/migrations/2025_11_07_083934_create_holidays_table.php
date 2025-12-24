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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('holidays')->insert([
            [
                'date' => '2025-01-01',
                'description' => 'Tahun Baru Masehi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-03-31',
                'description' => 'Nyepi (Tahun Baru Saka)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-06-01',
                'description' => 'Hari Lahir Pancasila',
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
        Schema::dropIfExists('holidays');
    }
};