<?php

use Carbon\Carbon;
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
        Schema::create('sesi_fotos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('session_time');
            $table->timestamps();
        });

        $start = Carbon::createFromTime(9, 0, 0);   // 09:00
        $end = Carbon::createFromTime(20, 0, 0);  // 20:00

        $data = [];
        $no = 1;

        while ($start <= $end) {
            $data[] = [
                'name' => (string) $no,
                'session_time' => $start->format('H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $start->addMinutes(30);
            $no++;
        }

        DB::table('sesi_fotos')->insert($data);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_fotos');
    }
};
