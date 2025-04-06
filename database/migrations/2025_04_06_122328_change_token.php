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
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            // Rename columns
            $table->boolean('created_from_mail_activation')->default(false);
        });

        // Rename columns
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            $table->renameColumn('token_expiration_date', 'expiration_date');
        });
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            $table->renameColumn('token_used', 'used');
        });
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            $table->renameColumn('token_used_date', 'used_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            // Rename columns
            $table->dropColumn('created_from_mail_activation');
        });
        // Rename columns
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            $table->renameColumn('expiration_date', 'token_expiration_date');
        });
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            $table->renameColumn('used', 'token_used');
        });
        Schema::table('slskey_reactivation_tokens', function (Blueprint $table) {
            $table->renameColumn('used_date', 'token_used_date');
        });
    }
};
