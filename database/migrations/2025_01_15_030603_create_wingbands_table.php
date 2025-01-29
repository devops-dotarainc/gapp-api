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
        Schema::create('wingbands', function (Blueprint $table) {
            $table->id();
            $table->string('stag_registry')->nullable();
            $table->string('breeder_name')->nullable();
            $table->string('farm_name')->nullable();
            $table->string('farm_address')->nullable();
            $table->string('province')->nullable();
            $table->string('wingband_number')->nullable();
            $table->string('feather_color')->nullable();
            $table->string('leg_color')->nullable();
            $table->string('comb_shape')->nullable();
            $table->string('nose_markings')->nullable();
            $table->string('feet_markings')->nullable();
            $table->string('season')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->date('wingband_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wingbands');
    }
};
