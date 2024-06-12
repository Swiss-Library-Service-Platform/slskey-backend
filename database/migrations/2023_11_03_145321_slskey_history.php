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
        Schema::create('slskey_histories', function (Blueprint $table) {
            $table->id();
            /*
            OLDEST
            $table->string('primary_id');
            $table->foreign('primary_id')->references('primary_id')->on('slskey_users')->onDelete('cascade');
            $table->string('slskey_code');
            $table->foreign('slskey_code')->references('slskey_code')->on('slskey_groups')->onDelete('cascade');
            */

            /*
            2nd OLDES
            $table->foreignId('slskey_user_id')->nullable()->constrained();
            $table->foreignId('slskey_group_id')->nullable()->constrained();
            */

            // NEWEST
            $table->unsignedBigInteger('slskey_user_id')->nullable();
            $table->unsignedBigInteger('slskey_group_id'); // Assuming it's a foreign key

            $table->foreign('slskey_user_id')->references('id')->on('slskey_users')->onDelete('cascade');
            $table->foreign('slskey_group_id')->references('id')->on('slskey_groups')->onDelete('cascade');

            $table->string('action');
            $table->string('author')->nullable();
            $table->string('trigger')->nullable();
            $table->boolean('success')->default(false);

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
        Schema::dropIfExists('slskey_histories');
    }
};
