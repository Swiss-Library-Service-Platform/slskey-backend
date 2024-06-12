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
        Schema::create('slskey_activations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('slskey_user_id');
            $table->unsignedBigInteger('slskey_group_id'); // Assuming it's a foreign key

            $table->foreign('slskey_user_id')->references('id')->on('slskey_users')->onDelete('cascade');
            $table->foreign('slskey_group_id')->references('id')->on('slskey_groups')->onDelete('cascade');

            $table->unique(['slskey_user_id', 'slskey_group_id']);

            $table->text('remark')->nullable();
            $table->boolean('activated')->default(false);
            $table->timestamp('activation_date')->nullable();

            $table->timestamp('expiration_date')->nullable();
            $table->timestamp('deactivation_date')->nullable();
            $table->boolean('expiration_disabled')->default(false);

            $table->boolean('blocked')->default(false);
            $table->timestamp('blocked_date')->nullable();

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
        Schema::dropIfExists('slskey_activations');
    }
};
