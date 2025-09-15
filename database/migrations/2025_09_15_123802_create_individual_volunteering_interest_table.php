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
        Schema::create('individual_volunteering_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('individual_id')
                ->constrained('individuals', 'id', 'fk_ind_vol_interest_individual')
                ->onDelete('cascade');
            $table->foreignId('volunteering_interest_id')
                ->constrained('volunteering_interests', 'id', 'fk_ind_vol_interest_volunteering')
                ->onDelete('cascade');
            $table->timestamps();
            $table->unique(['individual_id', 'volunteering_interest_id'], 'ind_vol_vol_interest_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_volunteering_interest');
    }
};
