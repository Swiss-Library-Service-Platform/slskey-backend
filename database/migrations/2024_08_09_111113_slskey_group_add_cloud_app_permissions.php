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
            $table->boolean('cloud_app_allow')->default(false)->after('webhook_mail_activation_days_token_validity');
            $table->string('cloud_app_roles')->nullable()->after('cloud_app_allow');
            $table->string('cloud_app_roles_scopes')->nullable()->after('cloud_app_roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->dropColumn([
                'cloud_app_allow',
                'cloud_app_roles',
                'cloud_app_roles_scopes',
            ]);
        });
    }
};
