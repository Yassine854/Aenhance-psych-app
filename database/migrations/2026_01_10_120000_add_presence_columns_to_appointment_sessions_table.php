<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointment_sessions', function (Blueprint $table) {
            $table->dateTime('patient_joined_at')->nullable()->after('room_id');
            $table->dateTime('psychologist_joined_at')->nullable()->after('patient_joined_at');
            $table->dateTime('patient_left_at')->nullable()->after('psychologist_joined_at');
            $table->dateTime('psychologist_left_at')->nullable()->after('patient_left_at');

            $table->boolean('patient_in_room')->default(false)->after('psychologist_left_at');
            $table->boolean('psychologist_in_room')->default(false)->after('patient_in_room');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'patient_joined_at',
                'psychologist_joined_at',
                'patient_left_at',
                'psychologist_left_at',
                'patient_in_room',
                'psychologist_in_room',
            ]);
        });
    }
};
