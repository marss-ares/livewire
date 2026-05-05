<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // label afisat
            $table->string('key');                           // slug unic in form
            $table->enum('type', [
                'text', 'email', 'number', 'date',
                'textarea', 'select', 'checkbox',
            ])->default('text');
            $table->json('options')->nullable();             // pentru select: ["opt1","opt2"]
            $table->boolean('required')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            // Index: "give me all columns for form X"
            $table->index('form_id');
            // Index: "sort columns by order"
            $table->index(['form_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_columns');
    }
};
