<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_entry_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('zinc'); // tailwind color name
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_entry_statuses');
    }
};
