<?php

use App\Models\Applicant;
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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards')->onDelete('cascade');
            $table->string('application_number')->unique();
            $table->string('admission_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('other_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('national_id')->unique()->nullable();
            $table->date('date_of_birth')->nullable();
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->foreignId('institution_id')->constrained('institutions')->onDelete('cascade');
            $table->string('course')->nullable();
            $table->decimal('amount_awarded', 15, 2)->default(0);
            $table->integer('need_assessment')->default(0);
            $table->decimal('fee_balance', 15, 2)->default(0);
            $table->string('orphan_status')->default(Applicant::ORPHAN_STATUS_NONE);
            $table->boolean('has_disabled_parent')->default(false)->after('orphan_status');
            $table->boolean('has_disability')->default(false)->after('has_disabled_parent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
