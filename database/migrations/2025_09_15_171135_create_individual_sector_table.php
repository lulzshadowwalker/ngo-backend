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
        Schema::create('individual_sector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('individual_id')
                ->constrained('individuals')
                ->onDelete('cascade');
            $table->foreignId('sector_id')
                ->constrained('sectors', 'id')
                ->onDelete('cascade');
            $table->timestamps();
            $table->unique(['individual_id', 'sector_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_sector');
    }
};
