<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // cine a completat
            $table->timestamps();

            // Index: "give me all entries for form X"
            $table->index('form_id');
            // Index: "give me all entries submitted by user X"
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_entries');
    }
};
