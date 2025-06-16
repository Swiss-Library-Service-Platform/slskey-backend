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
        Schema::table('slskey_groups', callback: function (Blueprint $table): void {
            $table->renameColumn('webhook_token_reactivation', 'mail_token_reactivation');
            $table->renameColumn('webhook_token_reactivation_days_send_before_expiry', 'mail_token_reactivation_days_send_before_expiry');
            $table->renameColumn('webhook_token_reactivation_days_token_validity', 'mail_token_reactivation_days_token_validity');
        });    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->renameColumn('mail_token_reactivation', 'webhook_token_reactivation');
            $table->renameColumn('mail_token_reactivation_days_send_before_expiry', 'webhook_token_reactivation_days_send_before_expiry');
            $table->renameColumn('mail_token_reactivation_days_token_validity', 'webhook_token_reactivation_days_token_validity');
        });
    }
};
