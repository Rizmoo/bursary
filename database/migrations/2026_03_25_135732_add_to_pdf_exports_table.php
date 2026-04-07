<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pdf_exports', function (Blueprint $table) {
            $table->foreignId('ward_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();
            $table->foreignId('financial_year_id')->nullable()->after('ward_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_exports', function (Blueprint $table) {
            //
        });
    }
};
