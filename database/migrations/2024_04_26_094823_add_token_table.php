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
        Schema::create('slskey_reactivation_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slskey_user_id');
            $table->unsignedBigInteger('slskey_group_id'); // Assuming it's a foreign key

            $table->foreign('slskey_user_id')->references('id')->on('slskey_users')->onDelete('cascade');
            $table->foreign('slskey_group_id')->references('id')->on('slskey_groups')->onDelete('cascade');

            $table->string('token', 255);
            $table->timestamp('token_expiration_date');
            $table->boolean('token_used')->default(false);
            $table->timestamp('token_used_date')->nullable();

            // Make slskey_user-id and slskey_group_id and token_used_date unique in combination
            $table->unique(['slskey_user_id', 'slskey_group_id', 'token_used_date'], 'unique_slskey_reactivation_tokens');

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
        Schema::dropIfExists('slskey_reactivation_tokens');
    }
};
