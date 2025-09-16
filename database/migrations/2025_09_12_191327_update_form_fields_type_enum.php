<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            // Change type column to use string instead of enum for flexibility
            $table->string('type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->enum('type', ['text', 'textarea', 'date', 'select', 'checkbox', 'file'])->change();
        });
    }
};
