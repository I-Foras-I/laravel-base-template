<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserExportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes - protected by auth and role middleware
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Roles management
    Route::resource('roles', RoleController::class);
    
    // Permissions management
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    
    // Users management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');
    
    // Users export
    Route::prefix('users/export')->name('users.export.')->group(function () {
        Route::get('pdf', [UserExportController::class, 'exportPdf'])->name('pdf');
        Route::get('excel', [UserExportController::class, 'exportExcel'])->name('excel');
        Route::get('csv', [UserExportController::class, 'exportCsv'])->name('csv');
    });

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
});

require __DIR__.'/settings.php';
