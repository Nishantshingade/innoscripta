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
        Schema::create('preference_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preference_id')->constrained('preferences')->onDelete('cascade');
            $table->string('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preference_sources');
    }
};
