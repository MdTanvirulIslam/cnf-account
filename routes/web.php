<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetPageTitle;
use App\Http\Controllers\BankBookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ImportBillController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\ExportBillController;
use App\Http\Middleware\CheckDomain;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankBookReportController;
use App\Http\Controllers\ExpenseReportController;
use App\Http\Controllers\ImportBillReportController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth','verified', CheckDomain::class, SetPageTitle::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('accounts', AccountController::class);

    Route::resource('bankbooks', BankBookController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('expenses', ExpensesController::class);
    Route::get('get-subcategories/{id}', [ExpensesController::class, 'getSubCategories']);

    Route::resource('employees', EmployeeController::class);



    // Import Employee Transactions
    Route::get('transactions/import',[TransactionController::class,'index'])->name('transactions.import');
    Route::post('transactions/import',[TransactionController::class,'store'])->name('transactions.import.store');
    Route::get('transactions/import/{id}/edit',[TransactionController::class,'edit'])->name('transactions.import.edit');
    Route::put('transactions/import/{id}',[TransactionController::class,'update'])->name('transactions.import.update');
    Route::delete('transactions/import/{id}',[TransactionController::class,'destroy'])->name('transactions.import.destroy');

    // Export Employee Transactions
    Route::get('transactions/export',[TransactionController::class,'index'])->name('transactions.export');
    Route::post('transactions/export',[TransactionController::class,'store'])->name('transactions.export.store');
    Route::get('transactions/export/{id}/edit',[TransactionController::class,'edit'])->name('transactions.export.edit');
    Route::put('transactions/export/{id}',[TransactionController::class,'update'])->name('transactions.export.update');
    Route::delete('transactions/export/{id}',[TransactionController::class,'destroy'])->name('transactions.export.destroy');

    Route::resource('import-bills', ImportBillController::class);
    Route::get('import-bills-data', [ImportBillController::class, 'data'])->name('import-bills.data');

    Route::resource('buyers', BuyerController::class);

    Route::resource('export-bills', ExportBillController::class);
    Route::get('export-bills-data',[ExportBillController::class,'data'])->name('export-bills.data');

    Route::get('/bankbook/report', [BankBookReportController::class, 'index'])->name('bankbook.report');
    Route::get('/expense/report', [ExpenseReportController::class, 'index'])->name('expense.report');
    Route::get('/import/bill/report',[ImportBillReportController::class,'index'])->name('import.bill.report');
    Route::get('/import-bill/dependent-options', [ImportBillReportController::class, 'getDependentOptions'])->name('importBill.dependent');



});

require __DIR__.'/auth.php';



