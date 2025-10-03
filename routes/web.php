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
use App\Http\Controllers\ExportBillReportController;
use App\Http\Controllers\ImportBillSummaryReportController;
use App\Http\Controllers\ExportBillSummaryReportController;
use App\Http\Controllers\EmployeeCashReportController;
use App\Http\Controllers\SummaryReportController;
use App\Http\Controllers\EmployeeDailyCashReportController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\YearlyReportController;


Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', [YearlyReportController::class, 'index'])->name('dashboard');
});

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
    // routes/web.php
    Route::get('/import-bills/{id}/print', [ImportBillController::class, 'print'])->name('import-bills.print');


    Route::resource('buyers', BuyerController::class);

    Route::resource('export-bills', ExportBillController::class);
    Route::get('export-bills-data',[ExportBillController::class,'data'])->name('export-bills.data');
    Route::get('/export-bills/{id}/print', [ExportBillController::class, 'print'])->name('export-bills.print');

    Route::get('/bankbook/report', [BankBookReportController::class, 'index'])->name('bankbook.report');
    Route::get('/expense/report', [ExpenseReportController::class, 'index'])->name('expense.report');
    Route::get('/import/bill/report',[ImportBillReportController::class,'index'])->name('import.bill.report');
    Route::get('/import-bill/dependent-options', [ImportBillReportController::class, 'getDependentOptions'])->name('importBill.dependent');

    Route::get('/export/bill/report',[ExportBillReportController::class,'index'])->name('export.bill.report');
    Route::get('/export-bill/dependent-options', [ExportBillReportController::class, 'getDependentOptions'])->name('exportBill.dependent');

    Route::get('/import/bill/summary/report',[ImportBillSummaryReportController::class,'index'])->name('import.bill.summary.report');
    Route::get('/export/bill/summary/report',[ExportBillSummaryReportController::class,'index'])->name('export.bill.summary.report');
    Route::get('/employee/cash/report',[EmployeeCashReportController::class,'index'])->name('employee.cash.report');
    Route::post('/employee/cash/report/filter', [EmployeeCashReportController::class, 'filter'])->name('employee-cash-report.filter');

    Route::get('/employee-daily-cash-report', [EmployeeDailyCashReportController::class, 'index'])->name('employee-daily-cash-report.index');
    Route::post('/employee-daily-cash-report/filter', [EmployeeDailyCashReportController::class, 'filter'])->name('employee-daily-cash-report.filter');

    Route::get('/summary/report', [SummaryReportController::class, 'index'])->name('summary.report');

    Route::get('/chart-data', [ChartController::class, 'getExpenseData'])->name('chart.data');




});

require __DIR__.'/auth.php';



