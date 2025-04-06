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
            $table->renameColumn('webhook_token_reactivation_days_send_before_expiry', 'webhook_token_reactivation_days_send_before_expiry');
        });
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->renameColumn('webhook_token_reactivation_days_send_before_expiry', 'webhook_token_reactivation_days_send_before_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->renameColumn('webhook_token_reactivation_days_send_before_expiry', 'webhook_token_reactivation_days_send_before_expiry');
            $table->renameColumn('webhook_token_reactivation_days_token_validity', 'webhook_token_reactivation_days_token_validity');
        });
    }
};
