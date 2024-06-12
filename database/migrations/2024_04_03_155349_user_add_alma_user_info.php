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
        Schema::table('users', function (Blueprint $table) {
            $table->string('alma_username')->nullable()->after('is_edu_id');
            $table->string('alma_institution')->nullable()->after('alma_username');
            // We apply unique constraint on the combination of alma_username and alma_institution
            $table->unique(['alma_username', 'alma_institution']);
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
