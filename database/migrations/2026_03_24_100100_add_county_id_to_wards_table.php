<?php

use App\Models\County;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            $table->foreignId('county_id')
                ->nullable()
                ->after('id')
                ->constrained('counties')
                ->nullOnDelete();
        });

        $defaultCountyId = County::query()->firstOrCreate([
            'name' => 'Unassigned County',
        ])->getKey();

        DB::table('wards')
            ->whereNull('county_id')
            ->update(['county_id' => $defaultCountyId]);

        Schema::table('wards', function (Blueprint $table) {
            $table->foreignId('county_id')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('county_id');
        });
    }
};
