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
        Schema::create('slskey_group_switch_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slskey_group_id');
            $table->unsignedBigInteger('switch_group_id');
            $table->timestamps();

            $table->foreign('slskey_group_id')->references('id')->on('slskey_groups')->onDelete('cascade');
            $table->foreign('switch_group_id')->references('id')->on('switch_groups')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('slskey_group_switch_group');
    }
};
