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
        Schema::table('volunteering_interests', function (Blueprint $table) {
            $table->dropForeign('volunteering_interests_individual_id_foreign');
            $table->dropColumn('individual_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteering_interests', function (Blueprint $table) {
            $table->foreignId('individual_id')->constrained('individuals')->nullable();
        });
    }
};
