<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPriceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditCodeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendNewPassword'])->middleware('throttle:5,1')->name('password.email');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/account/password', [AuthController::class, 'showChangePassword'])->name('password.edit');
    Route::put('/account/password', [AuthController::class, 'changePassword'])->name('password.change');

    // ---- Admin only ---------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        Route::resource('customers', CustomerController::class)->except('show');
        Route::get('customers/revenue-chart', [CustomerController::class, 'revenueChart'])->name('customers.chart');
        Route::get('customers/{customer}/orders', [CustomerController::class, 'completedOrders'])->name('customers.orders');
        Route::get('customers/{customer}/invoice', [CustomerController::class, 'invoice'])->name('customers.invoice');
        Route::post('customers/{customer}/lock', [CustomerController::class, 'lockOrders'])->name('customers.lock');
        Route::post('customers/{customer}/pay', [CustomerController::class, 'payOrders'])->name('customers.pay');

        Route::get('customers/{customer}/prices', [CustomerPriceController::class, 'index'])->name('customers.prices.index');
        Route::post('customers/{customer}/prices', [CustomerPriceController::class, 'store'])->name('customers.prices.store');
        Route::delete('customers/{customer}/prices/{price}', [CustomerPriceController::class, 'destroy'])->name('customers.prices.destroy');

        Route::resource('codes', EditCodeController::class)->except('show')->parameters(['codes' => 'code']);
        Route::resource('expenses', ExpenseController::class)->except('show');
        Route::get('profit', ProfitController::class)->name('profit');
    });

    // ---- Orders: Admin, CSR, QA ----------------------------------------
    Route::middleware('role:admin,csr,qa')->group(function () {
        Route::resource('orders', OrderController::class);
        Route::post('orders/{order}/verify', [OrderController::class, 'verify'])->middleware('role:qa')->name('orders.verify');
    });

    // ---- Designer work: complete orders -------------------------------
    Route::middleware('role:designer,csr')->group(function () {
        Route::get('work/complete', [WorkController::class, 'create'])->name('work.create');
        Route::post('work/complete', [WorkController::class, 'store'])->name('work.store');
        Route::get('work/search', [WorkController::class, 'search'])->name('work.search');
    });
    Route::get('work/completed', [WorkController::class, 'index'])->middleware('role:admin,designer,csr,qa')->name('work.index');
    Route::patch('work/{order}/stitches', [WorkController::class, 'updateStitches'])->middleware('role:admin,qa')->name('work.stitches');

    // ---- Users: Admin, QA ----------------------------------------------
    Route::middleware('role:admin,qa')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::post('users/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('users.toggle');
        Route::get('users/{user}/report', [UserController::class, 'report'])->name('users.report');
        Route::get('users/{user}/report/excel', [UserController::class, 'reportExcel'])->name('users.report.excel');
        Route::get('users/{user}/chart', [UserController::class, 'chart'])->name('users.chart');
    });
});
