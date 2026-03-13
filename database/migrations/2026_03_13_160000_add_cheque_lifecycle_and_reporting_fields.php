<?php

use App\Models\InstitutionCheque;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_years', function (Blueprint $table) {
            $table->decimal('unutilised_amount', 15, 2)->default(0)->after('closing_balance');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->date('awarded_at')->nullable()->after('amount_awarded');
        });

        Schema::table('institution_cheques', function (Blueprint $table) {
            $table->string('status')->default(InstitutionCheque::STATUS_PENDING)->after('cheque_date');
            $table->timestamp('cleared_at')->nullable()->after('status');
            $table->timestamp('stale_at')->nullable()->after('cleared_at');
            $table->timestamp('returned_at')->nullable()->after('stale_at');
            $table->decimal('returned_amount', 15, 2)->default(0)->after('total_amount');
        });

        DB::table('applicants')
            ->where('amount_awarded', '>', 0)
            ->update(['awarded_at' => DB::raw('date(created_at)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institution_cheques', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'cleared_at',
                'stale_at',
                'returned_at',
                'returned_amount',
            ]);
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('awarded_at');
        });

        Schema::table('financial_years', function (Blueprint $table) {
            $table->dropColumn('unutilised_amount');
        });
    }
};
