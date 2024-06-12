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
        Schema::create('report_email_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('email_address')->index();

            // Connection to SLSKeyGroup
            $table->unsignedBigInteger('slskey_group_id'); // Assuming it's a foreign key
            $table->foreign('slskey_group_id')->references('id')->on('slskey_groups')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_email_addresses');
    }
};
