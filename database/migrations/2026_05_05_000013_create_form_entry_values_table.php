<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_entry_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_column_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            // Index: "give me all values for entry X"
            $table->index('form_entry_id');
            // Index: "give me all values for column X (e.g. filter/sort by column)"
            $table->index('form_column_id');
            // Index: combined — the most common query
            $table->index(['form_entry_id', 'form_column_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_entry_values');
    }
};
