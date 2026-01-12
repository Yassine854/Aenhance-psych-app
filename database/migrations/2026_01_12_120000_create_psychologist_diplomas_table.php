<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychologist_diplomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psychologist_profile_id')
                  ->constrained('psychologist_profiles')
                  ->cascadeOnDelete();
            // store the diploma PDF URL/path
            $table->string('file_url');
            // optional original filename
            $table->timestamps();
        });

        if (Schema::hasColumn('psychologist_profiles', 'diploma')) {
            Schema::table('psychologist_profiles', function (Blueprint $table) {
                $table->dropColumn('diploma');
            });
        }
    }

    public function down(): void
    {
        // restore diploma column if it doesn't exist
        if (!Schema::hasColumn('psychologist_profiles', 'diploma')) {
            Schema::table('psychologist_profiles', function (Blueprint $table) {
                $table->string('diploma')->nullable();
            });
        }

        Schema::dropIfExists('psychologist_diplomas');
    }
};
