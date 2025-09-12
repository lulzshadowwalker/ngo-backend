<?php

use App\Enums\FormFieldType;
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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_form_id')->constrained()->cascadeOnDelete();
            $table->enum('type', FormFieldType::values());
            $table->json('label'); // translatable
            $table->json('placeholder')->nullable(); // translatable
            $table->json('help_text')->nullable(); // translatable
            $table->json('validation_rules')->nullable(); // array of validation rules
            $table->json('options')->nullable(); // translatable, for select/checkbox fields
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['application_form_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
