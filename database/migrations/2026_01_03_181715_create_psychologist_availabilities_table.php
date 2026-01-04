<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('psychologist_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psychologist_id')
                  ->constrained('psychologist_profiles')
                  ->cascadeOnDelete();

            $table->tinyInteger('day_of_week'); // 0 = Sunday, 6 = Saturday
            $table->time('start_time');          // Slot start
            $table->time('end_time');            // Slot end

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychologist_availabilities');
    }
};
