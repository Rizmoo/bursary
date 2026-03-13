<?php

use App\Http\Controllers\InstitutionChequePdfController;
use App\Http\Controllers\InstitutionChequeExcelController;
use App\Http\Controllers\InstitutionChequeWritingTemplateController;
use App\Http\Controllers\ApplicantsBeneficiariesExcelController;
use App\Http\Controllers\ApplicantsExcelController;
use App\Http\Controllers\ApplicantsFilteredExcelController;
use App\Http\Controllers\SetFinancialYearScopeController;
use App\Http\Controllers\QuarterlyReportExportController;
use App\Http\Controllers\SelfRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SelfRegistrationController::class, 'index'])->name('landing');
Route::get('/self-register', [SelfRegistrationController::class, 'create'])->name('self-register.create');
Route::post('/self-register', [SelfRegistrationController::class, 'store'])->name('self-register.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/admin/{tenant}/quarterly-reports/export', QuarterlyReportExportController::class)
        ->name('quarterly-reports.export');

    Route::get('/admin/{tenant}/institution-cheques/{institution_cheque}/pdf', InstitutionChequePdfController::class)
        ->name('institution-cheques.pdf');

    Route::get('/admin/{tenant}/institution-cheques/{institution_cheque}/excel', InstitutionChequeExcelController::class)
        ->name('institution-cheques.excel');

    Route::get('/admin/{tenant}/exports/institutions/cheque-writing-template', InstitutionChequeWritingTemplateController::class)
        ->name('institutions.cheque-writing-template');

    Route::get('/admin/{tenant}/exports/applicants/beneficiaries/excel', ApplicantsBeneficiariesExcelController::class)
        ->name('applicants.beneficiaries.excel');

    Route::get('/admin/{tenant}/exports/applicants/excel', ApplicantsExcelController::class)
        ->name('applicants.excel');

    Route::get('/admin/{tenant}/exports/applicants/filtered-excel', ApplicantsFilteredExcelController::class)
        ->name('applicants.filtered.excel');

    Route::post('/admin/{tenant}/financial-year-scope', SetFinancialYearScopeController::class)
        ->name('financial-year-scope.set');
});
