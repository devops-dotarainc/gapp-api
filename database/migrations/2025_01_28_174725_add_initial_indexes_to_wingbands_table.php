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
        Schema::table('wingbands', function (Blueprint $table) {
            $table->index(['wingband_number', 'deleted_at', 'created_at']);
            $table->index(['season', 'deleted_at', 'created_at']);
            $table->index(['breeder_name', 'deleted_at', 'created_at']);
            $table->index(['stag_registry', 'deleted_at', 'created_at']);
            $table->index(['status', 'deleted_at', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wingbands', function (Blueprint $table) {
            //
        });
    }
};
