<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('county_id')
                ->nullable()
                ->after('ward_id')
                ->constrained('counties')
                ->nullOnDelete();

            $table->boolean('is_county_admin')
                ->default(false)
                ->after('is_admin');
        });

        // Existing users can be backfilled by an explicit data migration/command.
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_county_admin');
            $table->dropConstrainedForeignId('county_id');
        });
    }
};
