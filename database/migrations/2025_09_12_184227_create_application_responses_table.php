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
        Schema::create('application_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained()->cascadeOnDelete();
            $table->json('value')->nullable(); // Flexible storage for any field type
            $table->string('file_path')->nullable(); // For file uploads
            $table->timestamps();

            // Indexes
            $table->index(['application_id', 'form_field_id']);
            $table->unique(['application_id', 'form_field_id']); // One response per field per application
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_responses');
    }
};
