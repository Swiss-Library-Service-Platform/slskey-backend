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
        Schema::create('switch_groups', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('slskey_group_id')->nullable()->constrained();
            $table->string('name');
            $table->string('switch_group_id');
            $table->text('publishers')->nullable();
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
        Schema::dropIfExists('slskey_history');
    }
};
