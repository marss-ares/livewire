<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // owner
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            // Index: "give me all forms for user X"
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
