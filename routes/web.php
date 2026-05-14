<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AssistanceLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrScannerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/verification/{token}', [BeneficiaryController::class, 'verification'])->name('beneficiaries.verification');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/beneficiaries/bulk', [BeneficiaryController::class, 'bulkAction'])->name('beneficiaries.bulk');
    Route::get('/beneficiaries/export/{format}', [BeneficiaryController::class, 'export'])->name('beneficiaries.export');
    Route::patch('/beneficiaries/{beneficiary}/archive', [BeneficiaryController::class, 'archive'])->name('beneficiaries.archive');
    Route::get('/beneficiaries/{beneficiary}/id-card', [BeneficiaryController::class, 'idCard'])->name('beneficiaries.id-card');
    Route::get('/beneficiaries/{beneficiary}/id-card.pdf', [BeneficiaryController::class, 'idCardPdf'])->name('beneficiaries.id-card.pdf');
    Route::post('/beneficiaries/{beneficiary}/assistance-logs', [AssistanceLogController::class, 'store'])->name('beneficiaries.assistance-logs.store');
    Route::resource('beneficiaries', BeneficiaryController::class);

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/qr-scanner', [QrScannerController::class, 'index'])->name('qr-scanner.index');
    Route::post('/qr-scanner/verify', [QrScannerController::class, 'verify'])->name('qr-scanner.verify');
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
    Route::post('/backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
});

require __DIR__.'/auth.php';
