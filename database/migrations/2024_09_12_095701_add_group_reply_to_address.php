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
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->string('reply_to')->nullable()->after('cloud_app_roles_scopes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_grouos', function (Blueprint $table) {
            $table->dropColumn('reply_to');
        });
    }
};
