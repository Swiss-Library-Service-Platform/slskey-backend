<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->boolean('webhook_persistent')->default(true)->after('webhook_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->dropColumn('webhook_persistent');
        });
    }
};
