<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender')->nullable();
            $table->string('relationship_to_patient', 100);
            $table->timestamps();

            $table->unique('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_beneficiaries');
    }
};