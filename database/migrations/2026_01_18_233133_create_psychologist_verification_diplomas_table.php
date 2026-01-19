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
        Schema::create('psychologist_verification_diplomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psychologist_verification_details_id')
                  ->constrained('psychologist_verification_details')
                  ->cascadeOnDelete()
                  ->name('fk_diplomas_details');
            $table->string('file_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychologist_verification_diplomas');
    }
};
