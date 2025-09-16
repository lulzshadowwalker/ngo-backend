<?php

use App\Enums\ProfileVisibility;
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
        Schema::create('individual_preferences', function (Blueprint $table) {
            $table->id();
            $table->enum('profile_visibility', ProfileVisibility::values())->default(ProfileVisibility::Public->value);
            $table->foreignId('individual_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_preferences');
    }
};
