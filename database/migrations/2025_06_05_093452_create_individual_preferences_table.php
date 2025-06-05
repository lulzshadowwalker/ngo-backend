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
        Schema::create('individual_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('email_notifications')->default('true');
            $table->string('push_notifications')->default('true');
            $table->string('language')->default('en');
            $table->enum('appearance', ["light","dark","system"])->default('system');
            $table->enum('profile_visibility', ["public","private"])->default('public');
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
