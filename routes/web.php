<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetPageTitle;
use App\Http\Controllers\BankBookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\EmployeeController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified',SetPageTitle::class])->name('dashboard');

Route::middleware(['auth', SetPageTitle::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('bankbooks', BankBookController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('expenses', ExpensesController::class);
    Route::get('get-subcategories/{id}', [ExpensesController::class, 'getSubCategories']);

    Route::resource('employees', EmployeeController::class);

});

require __DIR__.'/auth.php';



