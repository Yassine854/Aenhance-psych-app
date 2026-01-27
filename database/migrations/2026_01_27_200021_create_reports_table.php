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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            // Polymorphic reporter (patient or psychologist)
            $table->unsignedBigInteger('reporter_id');
            $table->string('reporter_type');

            // Polymorphic reported entity (patient or psychologist)
            $table->unsignedBigInteger('reported_id');
            $table->string('reported_type');

            // Required reason for the report
            $table->text('reason');

            // Optional proof image (storage path or URL)
            $table->string('proof_image')->nullable();

            // Administrative fields
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
