<?php

use App\Enums\Language;
use App\Enums\Appearance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("organization_preferences", function (Blueprint $table) {
            $table->id();
            $table->string("email_notifications")->default("true");
            $table->string("push_notifications")->default("true");
            $table
                ->enum("language", Language::values())
                ->default(Language::en->value);
            $table
                ->enum("appearance", Appearance::values())
                ->default(Appearance::system->value);
            $table->foreignId("organization_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("organization_preferences");
    }
};
