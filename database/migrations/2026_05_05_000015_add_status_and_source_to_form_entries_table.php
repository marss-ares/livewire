<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_entries', function (Blueprint $table) {
            $table->foreignId('status_id')
                ->nullable()
                ->constrained('form_entry_statuses')
                ->nullOnDelete()
                ->after('user_id');

            $table->string('source')->nullable()->after('status_id'); // original filename

            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::table('form_entries', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropIndex(['status_id']);
            $table->dropColumn(['status_id', 'source']);
        });
    }
};
