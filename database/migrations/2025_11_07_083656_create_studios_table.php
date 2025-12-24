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
        Schema::create('studios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->integer('capacity')->default(1);
            $table->timestamps();
        });

        DB::table('studios')->insert([
            [
                'name' => 'Studio A',
                'location' => 'Lantai 1 - Gedung Utama',
                'capacity' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Studio B',
                'location' => 'Lantai 2 - Gedung Utama',
                'capacity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Outdoor Garden',
                'location' => 'Taman Belakang Studio',
                'capacity' => 3,
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
        Schema::dropIfExists('studios');
    }
};
