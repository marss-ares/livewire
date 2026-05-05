<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_entry_statuses', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('id');

            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('form_entry_statuses', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};
