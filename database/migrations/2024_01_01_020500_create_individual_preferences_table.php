<?php

use App\Enums\Language;
use App\Enums\Appearance;
use App\Enums\ProfileVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("individual_preferences", function (Blueprint $table) {
            $table->id();
            $table->string("email_notifications")->default("true");
            $table->string("push_notifications")->default("true");
            $table
                ->enum("language", Language::values())
                ->default(Language::en->value);
            $table
                ->enum("appearance", Appearance::values())
                ->default(Appearance::system->value);
            $table
                ->enum("profile_visibility", ProfileVisibility::values())
                ->default(ProfileVisibility::public->value);
            $table->foreignId("individual_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("individual_preferences");
    }
};
