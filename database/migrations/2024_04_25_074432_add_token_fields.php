<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->boolean('webhook_mail_activation')->default(false)->after('send_activation_mail');
            $table->string('webhook_mail_activation_domains')->nullable()->after('webhook_mail_activation');
            $table->integer('webhook_mail_activation_days_send_before_expiry')->nullable()->after('webhook_mail_activation_domains');
            $table->integer('webhook_mail_activation_days_token_validity')->nullable()->after('webhook_mail_activation_days_send_before_expiry');
        });

        Schema::table('slskey_activations', function (Blueprint $table) {
            $table->string('webhook_activation_mail')->nullable()->after('blocked_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
