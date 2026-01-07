<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('psychologist_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');


            $table->enum('status', [
                'pending', 'confirmed', 'completed', 'cancelled', 'no_show'
            ])->default('pending');

            $table->decimal('price', 8, 2);
            $table->string('currency', 10)->default('TND');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
