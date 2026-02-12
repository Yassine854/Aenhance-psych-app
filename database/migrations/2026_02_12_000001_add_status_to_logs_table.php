<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('logs', 'status')) {
            Schema::table('logs', function (Blueprint $table) {
                $table->string('status')->nullable()->index()->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('logs', 'status')) {
            Schema::table('logs', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
