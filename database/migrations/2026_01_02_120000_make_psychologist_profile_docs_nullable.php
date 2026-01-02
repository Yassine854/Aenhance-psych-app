<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal for column changes.
        DB::statement("ALTER TABLE `psychologist_profiles` MODIFY `diploma` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `psychologist_profiles` MODIFY `cin` VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // If we roll back, normalize existing NULLs to empty string first.
        DB::statement("UPDATE `psychologist_profiles` SET `diploma` = '' WHERE `diploma` IS NULL");
        DB::statement("UPDATE `psychologist_profiles` SET `cin` = '' WHERE `cin` IS NULL");

        DB::statement("ALTER TABLE `psychologist_profiles` MODIFY `diploma` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `psychologist_profiles` MODIFY `cin` VARCHAR(255) NOT NULL");
    }
};
