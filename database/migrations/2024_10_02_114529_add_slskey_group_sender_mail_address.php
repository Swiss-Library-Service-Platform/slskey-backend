<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->string('mail_sender_address')->nullable()->after('show_member_educational_institution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slskey_groups', function (Blueprint $table) {
            $table->dropColumn('mail_sender_address');
        });
    }
};
