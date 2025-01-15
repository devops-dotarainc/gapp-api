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
        Schema::create('stags', function (Blueprint $table) {
            $table->id();
            $table->string('stag_registry')->nullable();
            $table->string('farm_name')->nullable();
            $table->string('farm_address')->nullable();
            $table->string('breeder_name')->nullable();
            $table->string('chapter')->nullable();
            $table->unsignedBigInteger('banded_cockerels')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stags');
    }
};
