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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('role')->nullable();
            $table->unsignedTinyInteger('status')->nullable();
            $table->string('module')->nullable();
            $table->string('controller')->nullable();
            $table->string('function')->nullable();
            $table->string('table_name')->nullable();
            $table->string('table_id')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('host')->nullable();
            $table->string('path')->nullable();
            $table->string('url')->nullable();
            $table->string('referer')->nullable();
            $table->string('method')->nullable();
            $table->string('ip')->nullable();
            $table->string('request')->nullable();
            $table->string('agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
