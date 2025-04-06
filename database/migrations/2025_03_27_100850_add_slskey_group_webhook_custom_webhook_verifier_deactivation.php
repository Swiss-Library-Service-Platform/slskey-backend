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
            $table->boolean('webhook_custom_verifier_deactivation')->nullbable()->after('webhook_custom_verifier');
            $table->boolean('webhook_token_reactivation')->nullbable()->after('webhook_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->dropColumn('webhook_custom_verifier_deactivation');
            $table->dropColumn('webhook_token_reactivation');
        });
    }
};
