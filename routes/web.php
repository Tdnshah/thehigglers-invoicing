<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationNoteController;
use App\Http\Controllers\InvoiceNoteController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\SettingsController;
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
        Route::post('/invoices/{invoice}/approve', [InvoiceController::class, 'approve'])->name('invoices.approve');
        Route::post('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
        Route::resource('invoices', InvoiceController::class);
        
        // Quotations
        Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
        Route::get('/quotations/{quotation}/download', [QuotationController::class, 'downloadPdf'])->name('quotations.download');
        Route::post('/quotations/{quotation}/revisions', [QuotationController::class, 'createRevision'])->name('quotations.revisions');
        Route::post('/quotations/{quotation}/mark-as-active', [QuotationController::class, 'markAsActive'])->name('quotations.mark-as-active');
        Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert');
        Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status');
        Route::resource('quotations', QuotationController::class);
        
        // Internal notes, on both documents
        Route::post('/quotations/{quotation}/notes', [QuotationNoteController::class, 'store'])->name('quotations.notes.store');
        Route::delete('/quotations/notes/{note}', [QuotationNoteController::class, 'destroy'])->name('quotations.notes.destroy');
        Route::post('/invoices/{invoice}/notes', [InvoiceNoteController::class, 'store'])->name('invoices.notes.store');
        Route::delete('/invoices/notes/{note}', [InvoiceNoteController::class, 'destroy'])->name('invoices.notes.destroy');
        
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Settings, one addressable section per screen
        Route::get('/settings', fn () => redirect()->route('settings.show', 'company'))->name('settings.index');
        Route::get('/settings/{section}', [SettingsController::class, 'show'])->name('settings.show');
        Route::patch('/settings/{section}', [SettingsController::class, 'update'])->name('settings.update');

        // Legacy entry point, kept so old links and bookmarks still land somewhere.
        Route::get('/company/settings', fn () => redirect()->route('settings.show', 'company'))->name('company.edit');

        // Payments
        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    });

    require __DIR__.'/auth.php';
});
