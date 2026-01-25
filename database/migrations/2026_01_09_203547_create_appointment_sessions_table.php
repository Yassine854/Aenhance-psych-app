<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique('appointment_id');

            $table->string('room_id')->unique();
            
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            $table->unsignedSmallInteger('duration_minutes')->nullable();

            $table->dateTime('patient_joined_at')->nullable();
            $table->dateTime('psychologist_joined_at')->nullable();
            $table->dateTime('patient_left_at')->nullable();
            $table->dateTime('psychologist_left_at')->nullable();

            $table->boolean('patient_in_room')->default(false);
            $table->boolean('psychologist_in_room')->default(false);

            $table->enum('status', [
                'active',
                'completed',
                'interrupted'
            ])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_sessions');
    }
};
