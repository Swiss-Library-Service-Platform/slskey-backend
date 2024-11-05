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
        Schema::create('slskey_report_counts', function (Blueprint $table) {
            
            $table->unsignedBigInteger('slskey_group_id'); 
            $table->foreign('slskey_group_id')->references('id')->on('slskey_groups')->onDelete('cascade');

            $table->id();
            $table->string('month');
            $table->string('year');
            $table->integer('activated_count');
            $table->integer('extended_count');
            $table->integer('reactivated_count');
            $table->integer('deactivated_count');
            $table->integer('blocked_active_count');
            $table->integer('blocked_inactive_count');
            $table->integer('monthly_change_count');

            $table->integer('total_active_users');
            $table->integer('total_active_educational_users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slskey_report_counts');
    }
};
