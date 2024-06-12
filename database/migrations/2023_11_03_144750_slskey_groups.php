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
        Schema::create('slskey_groups', function (Blueprint $table) {
            $table->id();
            $table->string('slskey_code')->index()->unique();
            $table->string('name');
            $table->string('workflow');
            $table->string('alma_iz');
            $table->string('webhook_secret')->nullable();
            $table->boolean('webhook_custom_verifier')->nullbable()->default(false);
            $table->string('webhook_custom_verifier_class')->nullable();
            $table->integer('days_activation_duration')->nullable();
            $table->integer('days_expiration_reminder')->nullable();
            $table->boolean('send_activation_mail')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::drop('slskey_groups');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
