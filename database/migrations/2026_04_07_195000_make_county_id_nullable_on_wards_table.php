<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            // Allow standalone wards with no county affiliation.
            $table->foreignId('county_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            $table->foreignId('county_id')
                ->nullable(false)
                ->change();
        });
    }
};
