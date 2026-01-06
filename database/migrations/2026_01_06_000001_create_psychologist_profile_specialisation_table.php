<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychologist_profile_specialisation', function (Blueprint $table) {
            $table->id();

            $table->foreignId('psychologist_profile_id')
                ->constrained('psychologist_profiles', 'id', 'pps_profile_fk')
                ->cascadeOnDelete();

            $table->foreignId('specialisation_id')
                ->constrained('specialisations', 'id', 'pps_specialisation_fk')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['psychologist_profile_id', 'specialisation_id'], 'pps_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychologist_profile_specialisation');
    }
};
