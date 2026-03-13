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
        Schema::create('institution_cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards')->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained('financial_years')->cascadeOnDelete();
            $table->string('cheque_number')->unique();
            $table->date('cheque_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('applicant_institution_cheque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_cheque_id')->constrained('institution_cheques')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('applicant_id');
            $table->unique(['institution_cheque_id', 'applicant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_institution_cheque');
        Schema::dropIfExists('institution_cheques');
    }
};
