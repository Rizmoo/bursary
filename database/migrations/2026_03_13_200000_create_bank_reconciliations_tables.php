<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards')->cascadeOnDelete();
            $table->foreignId('financial_year_id')->nullable()->constrained('financial_years')->nullOnDelete();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->date('statement_period_start')->nullable();
            $table->date('statement_period_end')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->string('file_path');
            $table->string('status')->default('draft'); // draft, applied
            $table->unsignedInteger('total_cheques_cleared')->default(0);
            $table->unsignedInteger('total_cheques_bounced')->default(0);
            $table->decimal('total_cleared_amount', 15, 2)->default(0);
            $table->decimal('total_bounced_amount', 15, 2)->default(0);
            $table->decimal('total_penalties', 15, 2)->default(0);
            $table->decimal('total_bank_charges', 15, 2)->default(0);
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained('bank_reconciliations')->cascadeOnDelete();
            $table->date('transaction_date')->nullable();
            $table->date('value_date')->nullable();
            $table->text('description');
            $table->decimal('money_out', 15, 2)->default(0);
            $table->decimal('money_in', 15, 2)->default(0);
            $table->decimal('ledger_balance', 15, 2)->default(0);
            $table->string('type'); // cheque_cleared, cheque_bounced, bounced_reversal, penalty, bank_charge, balance_bfwd, balance_end, deposit, other
            $table->string('cheque_number')->nullable();
            $table->foreignId('institution_cheque_id')->nullable()->constrained('institution_cheques')->nullOnDelete();
            $table->boolean('is_matched')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
        Schema::dropIfExists('bank_reconciliations');
    }
};
