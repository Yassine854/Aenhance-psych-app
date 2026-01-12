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
        Schema::create('psychologist_profile_expertise', function (Blueprint $table) {
            $table->foreignId('psychologist_profile_id')->constrained('psychologist_profiles')->cascadeOnDelete();
            $table->foreignId('expertise_id')->constrained('expertises')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['psychologist_profile_id', 'expertise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychologist_profile_expertise');
    }
};
