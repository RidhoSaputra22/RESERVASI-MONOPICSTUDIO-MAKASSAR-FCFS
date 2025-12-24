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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(60);
            $table->timestamps();
        });

        DB::table('packages')->insert([
            [

                'name' => 'Couple Session',
                'description' => 'Paket foto untuk pasangan selama 1 jam.',
                'price' => 500000,
                'duration_minutes' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Family Package',
                'description' => 'Paket foto keluarga dengan durasi 90 menit.',
                'price' => 750000,
                'duration_minutes' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Prewedding Session',
                'description' => 'Paket prewedding lengkap dengan konsep outdoor.',
                'price' => 1200000,
                'duration_minutes' => 120,
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
        Schema::dropIfExists('packages');
    }
};