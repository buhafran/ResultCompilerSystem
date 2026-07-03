<?php
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ResultViewController;
use App\Http\Controllers\Web\SchoolLandingController;
use App\Http\Controllers\Web\StudentPortalController;
use App\Http\Controllers\Web\StudentDataController;
use Illuminate\Support\Facades\Route;
Route::get('/', HomeController::class)->name('home');
Route::get('/schools/{school:slug}',SchoolLandingController::class)->name('school.landing');
Route::get('/schools/{school:slug}/portal',[StudentPortalController::class,'loginForm'])->name('school.portal.login');
Route::post('/schools/{school:slug}/portal',[StudentPortalController::class,'authenticate'])->middleware('throttle:8,1')->name('school.portal.authenticate');
Route::get('/schools/{school:slug}/results',[StudentPortalController::class,'results'])->name('school.portal.results');
Route::post('/schools/{school:slug}/logout',[StudentPortalController::class,'logout'])->name('school.portal.logout');
Route::get('/results/{summary:public_token}',[ResultViewController::class,'show'])->name('results.show');
Route::get('/results/{summary:public_token}/pdf',[ResultViewController::class,'pdf'])->name('results.pdf');
Route::get('/admin/result-publications/{publication}/preview',[ResultViewController::class,'preview'])->middleware('auth')->name('results.publication.preview');
Route::middleware('auth')->group(function (): void {
    Route::get('/admin/result-publications/{publication}/broadsheet.pdf', [ResultViewController::class, 'broadsheetPdf'])->name('results.publication.broadsheet');
    Route::get('/admin/result-publications/{publication}/report-cards.pdf', [ResultViewController::class, 'classReportCardsPdf'])->name('results.publication.report-cards');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/admin/schools/{school:slug}/students/template', [StudentDataController::class, 'template'])->name('students.template');
    Route::get('/admin/schools/{school:slug}/students/export', [StudentDataController::class, 'export'])->name('students.export');
});
