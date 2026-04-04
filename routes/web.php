<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationNoteController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckInstallation;

use App\Http\Controllers\DashboardController;

Route::middleware([CheckInstallation::class])->group(function () {
    // Installation Routes
    Route::get('/install', [InstallController::class, 'index'])->name('install.index');
    Route::post('/install', [InstallController::class, 'store'])->name('install.store');

    Route::get('/', function () {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return redirect()->route('login');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::resource('clients', ClientController::class);
        
        // Client User Management Routes
        Route::get('/clients/{client}/user/create', [ClientController::class, 'createUser'])->name('clients.user.create');
        Route::post('/clients/{client}/user', [ClientController::class, 'storeUser'])->name('clients.user.store');
        
        Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
        Route::resource('invoices', InvoiceController::class);
        
        // Quotations
        Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
        Route::get('/quotations/{quotation}/download', [QuotationController::class, 'downloadPdf'])->name('quotations.download');
        Route::post('/quotations/{quotation}/revisions', [QuotationController::class, 'createRevision'])->name('quotations.revisions');
        Route::post('/quotations/{quotation}/mark-as-active', [QuotationController::class, 'markAsActive'])->name('quotations.mark-as-active');
        Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert');
        Route::resource('quotations', QuotationController::class);
        
        // Quotation Notes
        Route::post('/quotations/{quotation}/notes', [QuotationNoteController::class, 'store'])->name('quotations.notes.store');
        
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Company Settings
        Route::get('/company/settings', [CompanyController::class, 'edit'])->name('company.edit');
        Route::patch('/company/settings', [CompanyController::class, 'update'])->name('company.update');

        // Payments
        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    });

    require __DIR__.'/auth.php';
});
