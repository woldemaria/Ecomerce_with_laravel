<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::get('/products/bulk-update', [ProductController::class, 'bulkUpdate'])->name('products.bulk-page');
    Route::post('/products/bulk', [ProductController::class, 'bulkUpdate'])->name('products.bulk');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/products/bulk-manage', function () {
        return Inertia::render('Products/BulkManage');
    })->name('products.bulk-manage');
    Route::resource('products', ProductController::class);

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/adjust', [InventoryController::class, 'adjustmentScreen'])->name('inventory.adjust');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust.store');
    Route::post('/inventory/bulk-adjust', [InventoryController::class, 'bulkAdjust'])->name('inventory.bulk-adjust');
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');

    // Reports
    Route::get('/reports/performance', [ReportsController::class, 'performance'])->name('reports.performance');
    Route::get('/reports/search', [ReportsController::class, 'search'])->name('reports.search');


    // Settings (Screen 12)
    Route::get('/settings', function () {
        return Inertia::render('Settings/Index', ['user' => auth()->user()]);
    })->name('settings');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
