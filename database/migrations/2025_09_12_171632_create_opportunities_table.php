<?php

use App\Enums\OpportunityStatus;
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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // translatable
            $table->json('description'); // translatable
            $table->enum('status', OpportunityStatus::values())->default(OpportunityStatus::Active->value);
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->json('tags')->nullable();
            $table->integer('duration')->nullable();
            $table->date('expiry_date')->nullable();
            $table->json('about_the_role')->nullable();
            $table->json('key_responsibilities')->nullable();
            $table->json('required_skills')->nullable();
            $table->json('time_commitment')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('location_description')->nullable();
            $table->json('benefits')->nullable();
            $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['program_id', 'status']);
            $table->index(['sector_id', 'status']);
            $table->index(['location_id', 'status']);
            $table->index(['expiry_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
