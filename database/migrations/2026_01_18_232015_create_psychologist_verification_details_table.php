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
        Schema::create('psychologist_verification_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('psychologist_profile_id')
                  ->constrained('psychologist_profiles')
                  ->cascadeOnDelete();
            
          $table->string('rib');
          $table->string('bank_name');
          $table->string('bank_account_number');
          $table->string('bank_account_name');
          $table->string('rib_file_url');

          $table->string('identity_type');
          $table->string('identity_number');
          $table->string('identity_file_url');

          
          $table->text('rejection_reason')->nullable();

          $table->enum('verification_status', ['pending', 'approved', 'rejected'])
          ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychologist_verification_details');
    }
};
