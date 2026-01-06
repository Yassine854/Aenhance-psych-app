<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychologist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('diploma'); // license ,master
            $table->string('cin'); //Proof
            $table->string('cv');
            $table->string('gender')->nullable();
            $table->string('country');
            $table->string('city');
            $table->string('phone');
            $table->string('country_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth');
            $table->json('languages');
            $table->text('bio')->nullable();
            $table->decimal('price_per_session', 8, 2);
            $table->boolean('is_approved')->default(false);
            $table->string('profile_image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychologist_profiles');
    }
};
