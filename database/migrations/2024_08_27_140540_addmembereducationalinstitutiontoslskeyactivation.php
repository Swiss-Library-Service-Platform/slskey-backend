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
        Schema::table('slskey_activations', function (Blueprint $table) {
            $table->boolean('member_educational_institution')->nullable()->after('webhook_activation_mail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_activations', function (Blueprint $table) {
            $table->dropColumn('member_educational_institution');
        });
    }
};
