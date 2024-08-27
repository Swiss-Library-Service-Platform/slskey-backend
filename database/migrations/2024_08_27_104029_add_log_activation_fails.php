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
        Schema::create('log_activation_fails', function (Blueprint $table) {
            $table->id();
            $table->string('primary_id');
            $table->string('action');
            $table->string('message');
            $table->string('author')->nullable();
            $table->timestamp('logged_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activation_fails');
    }
};
