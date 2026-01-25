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
        Schema::create('appointment_session_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_session_id')
                ->constrained('appointment_sessions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('psychologist_id')->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->dateTime('session_date');
            $table->unsignedSmallInteger('session_duration');

            $table->string(column: 'session_mode');

            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('intervention')->nullable();

            $table->string(column: 'risk_level');

            $table->text('plan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_session_notes');
    }
};
