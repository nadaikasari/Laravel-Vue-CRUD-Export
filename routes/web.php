<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//tidak menggunkan middleware karena belum tersetting middlewarenya
Route::group(['prefix' => 'orders'], function () {
    Route::get('/', [TransactionController::class, 'index'])->name('orders.index');
    Route::get('/create', [TransactionController::class, 'create'])->name('orders.create');
    Route::get('/{orderId}/edit', [TransactionController::class, 'update'])->name('orders.update-view');
    Route::put('/{orderId}', [TransactionController::class, 'updateOrderTransaction'])->name('orders.update');
    Route::post('/', [TransactionController::class, 'generateTransaction'])->name('orders.create-transaction');
    Route::delete('/{orderId}', [TransactionController::class, 'deleteOrder'])->name('orders.delete-order');
    Route::get('/export', [TransactionController::class, 'exportTransactionToXlsx'])->name('orders.exports');
    Route::get('/download/{fileName}', [TransactionController::class, 'downloadFileExcel'])->name('orders.download');
});

require __DIR__.'/auth.php';
