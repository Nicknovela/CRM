<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerticalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile
    Route::view('profile', 'profile')->name('profile');

    // Clients
    Route::resource('clients', ClientController::class);

    // Deals
    Route::resource('deals', DealController::class);
    Route::delete('deals/{deal}/force', [DealController::class, 'forceDestroy'])
        ->name('deals.force-destroy')
        ->withTrashed()
        ->middleware('permission:deals.force-delete');
    Route::patch('deals/{deal}/restore', [DealController::class, 'restore'])
        ->name('deals.restore')
        ->withTrashed()
        ->middleware('permission:deals.restore');

    // Kanban
    Route::get('/kanban', KanbanController::class)->name('kanban');

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', ReportController::class)->name('index');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
    });

    // Internal API (used by Livewire/JS)
    Route::prefix('api/internal')->name('internal.')->group(function () {
        Route::get('stages/{vertical}', [StageController::class, 'byVertical'])->name('stages.by-vertical');
        Route::patch('stages/reorder', [StageController::class, 'reorder'])->name('stages.reorder');
        Route::patch('deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('verticals', VerticalController::class);
        Route::resource('users', UserController::class);
    });

});
