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
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
            $table->dropColumn([
                'featured_image_alt',
                'status',
                'meta_title',
                'meta_description',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('featured_image_alt')->nullable()->after('featured_image');
            $table->enum('status', ['draft', 'published'])->default('draft')->after('featured_image_alt');
            $table->string('meta_title')->nullable()->after('category');
            $table->text('meta_description')->nullable()->after('meta_title');

            $table->index(['status', 'published_at']);
        });
    }
};