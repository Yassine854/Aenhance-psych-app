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
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('psychologist_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');

            $table->enum('status', [
                'pending', 'confirmed', 'completed', 'cancelled', 'no_show'
            ])->default('pending');

            $table->enum('canceled_by', ['patient', 'psychologist', 'admin'])->nullable();
            $table->foreignId('canceled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason')->nullable();
            $table->dateTime('canceled_at')->nullable();
           
            $table->enum('no_show_by', ['patient', 'psychologist'])->nullable();
            $table->foreignId('no_show_user_id')->nullable()->constrained('users')->nullOnDelete();

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
